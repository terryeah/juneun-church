<?php

namespace App\Console\Commands;

use App\Models\Sermon;
use App\Models\ServiceType;
use App\Models\SiteSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Imports recent sermon recordings from the church YouTube channel.
 *
 * The channel's public RSS feed is used, so no API key is required. Only
 * uploads titled with the sermon marker (주일설교) are imported; shorts
 * and clips are ignored. Titles in the channel's format
 * "[주일설교] 제목ㅣ본문ㅣ설교자ㅣ날짜" are split into their parts.
 * Already-imported videos are recognised by their YouTube id, so the
 * command can run repeatedly (for example from the scheduler).
 */
class ImportYoutubeSermons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'youtube:import {--channel= : Channel id or handle URL, defaults to the youtube_url site setting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import recent sermon uploads from the church YouTube channel RSS feed';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $channelId = $this->resolveChannelId($this->option('channel') ?: SiteSetting::get('youtube_url'));

        if (blank($channelId)) {
            $this->error('Could not resolve a YouTube channel id. Set the youtube_url site setting or pass --channel.');

            return self::FAILURE;
        }

        $response = Http::get('https://www.youtube.com/feeds/videos.xml', ['channel_id' => $channelId]);

        if (! $response->successful()) {
            $this->error("YouTube feed request failed with status {$response->status()}.");

            return self::FAILURE;
        }

        $entries = $this->parseFeed($response->body());
        $sermons = collect($entries)->filter(fn (array $entry) => Str::contains($entry['title'], '주일설교'));

        if ($sermons->isEmpty()) {
            $this->warn('No sermon uploads found in the feed.');

            return self::SUCCESS;
        }

        $serviceTypeId = ServiceType::query()->where('name', '주일예배')->value('id')
            ?? ServiceType::query()->orderBy('sort_order')->value('id');

        $imported = 0;

        foreach ($sermons as $entry) {
            if (Sermon::query()->where('youtube_video_id', $entry['video_id'])->exists()) {
                continue;
            }

            $parts = $this->parseTitle($entry['title']);

            Sermon::query()->create([
                'title' => $parts['title'],
                'youtube_video_id' => $entry['video_id'],
                'preacher' => $parts['preacher'],
                'sermon_date' => $entry['published'],
                'service_type_id' => $serviceTypeId,
                'scripture_reference' => $parts['scripture'],
                'is_published' => true,
            ]);

            $imported++;
        }

        $this->info("Imported {$imported} new sermon(s) from YouTube.");

        return self::SUCCESS;
    }

    /**
     * Resolve a channel id from a channel URL, handle URL or raw id.
     */
    private function resolveChannelId(?string $source): ?string
    {
        if (blank($source)) {
            return null;
        }

        if (preg_match('/(UC[A-Za-z0-9_-]{22})/', $source, $matches)) {
            return $matches[1];
        }

        $response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($source);

        if ($response->successful() && preg_match('/"externalId":"(UC[A-Za-z0-9_-]{22})"/', $response->body(), $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Parse the Atom feed into video id, title and publish date rows.
     *
     * @return array<int, array{video_id: string, title: string, published: Carbon}>
     */
    private function parseFeed(string $xml): array
    {
        $document = simplexml_load_string($xml);

        if ($document === false) {
            return [];
        }

        $entries = [];

        foreach ($document->entry as $entry) {
            $yt = $entry->children('http://www.youtube.com/xml/schemas/2015');

            $entries[] = [
                'video_id' => (string) $yt->videoId,
                'title' => (string) $entry->title,
                'published' => Carbon::parse((string) $entry->published),
            ];
        }

        return $entries;
    }

    /**
     * Split a channel-format title into sermon title, scripture and preacher.
     *
     * @return array{title: string, scripture: ?string, preacher: ?string}
     */
    private function parseTitle(string $raw): array
    {
        $segments = collect(preg_split('/[ㅣ|]/u', $raw))
            ->map(fn (string $segment) => trim($segment))
            ->filter();

        $title = Str::of($segments->first() ?? $raw)
            ->replaceMatches('/\[[^\]]*\]/u', '')
            ->squish()
            ->value();

        $scripture = $segments->first(fn (string $segment) => preg_match('/\d+:\d+/', $segment));
        $preacher = $segments->first(fn (string $segment) => Str::contains($segment, ['목사', '전도사', '강도사']));

        return [
            'title' => $title !== '' ? $title : $raw,
            'scripture' => $scripture,
            'preacher' => $preacher ? Str::of($preacher)->replace('목사', ' 목사')->squish()->value() : null,
        ];
    }
}
