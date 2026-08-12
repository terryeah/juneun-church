<?php

namespace App\Filament\Resources\Videos\Schemas;

use App\Models\Album;
use App\Models\Video;
use Closure;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

/**
 * Form schema for a video in an album.
 *
 * Adding a video is pasting the YouTube link and giving it a name. The
 * church uploads to its own channel, so nothing is uploaded here and
 * the file itself is never this site's business.
 */
class VideoForm
{
    /**
     * Configure the video form.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('album_id')
                    ->label('앨범')
                    ->relationship(
                        'album',
                        'title',
                        fn (Builder $query): Builder => $query->ofType(Album::TYPE_VIDEO)->orderByDesc('event_date'),
                    )
                    ->required()
                    ->searchable()
                    ->preload()
                    ->helperText('동영상 앨범만 나옵니다. 없으면 앨범에서 종류를 동영상으로 두고 먼저 만드세요.'),

                TextInput::make('title')
                    ->label('제목')
                    ->required()
                    ->maxLength(255)
                    ->helperText('사이트에 보일 이름입니다. 유튜브 제목과 달라도 됩니다.'),

                /**
                 * Stored as the identifier, entered as whatever the
                 * church has in hand. It is normalised as soon as the
                 * field is left, so the still below either appears or
                 * does not while there is still time to fix it.
                 */
                TextInput::make('youtube_id')
                    ->label('유튜브 주소')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set(
                        'youtube_id',
                        Video::extractYoutubeId($state) ?? $state,
                    ))
                    ->dehydrateStateUsing(fn (?string $state): ?string => Video::extractYoutubeId($state))
                    ->rules([
                        fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                            if (Video::extractYoutubeId(is_string($value) ? $value : null) === null) {
                                $fail('유튜브 주소를 알아볼 수 없습니다. 주소를 통째로 붙여넣으셔도 됩니다.');
                            }
                        },
                    ])
                    ->helperText('유튜브에서 복사한 주소를 그대로 붙여넣으세요. 주소에서 영상 번호만 뽑아 저장합니다.'),

                /**
                 * The still is the check. A wrong identifier plays the
                 * wrong video, and nobody catches that by reading
                 * eleven characters of text.
                 */
                Placeholder::make('preview')
                    ->label('미리보기')
                    ->content(fn (Get $get): HtmlString => static::preview($get('youtube_id'))),

                Textarea::make('description')
                    ->label('설명')
                    ->rows(4)
                    ->columnSpanFull(),

                TextInput::make('sort_order')
                    ->label('순서')
                    ->numeric()
                    ->placeholder('맨 뒤')
                    ->helperText('작은 숫자가 앞에 옵니다. 비워두면 맨 뒤에 붙습니다.'),
            ]);
    }

    /**
     * The still for whatever is currently in the address field.
     */
    private static function preview(mixed $state): HtmlString
    {
        $id = Video::extractYoutubeId(is_string($state) ? $state : null);

        if ($id === null) {
            return new HtmlString('<span class="fi-color-gray">주소를 넣으면 여기에 영상 미리보기가 나옵니다.</span>');
        }

        return new HtmlString(
            '<img src="https://i.ytimg.com/vi/'.e($id).'/mqdefault.jpg" alt="" '
            .'style="width:100%;max-width:18rem;border-radius:.625rem" loading="lazy">'
        );
    }
}
