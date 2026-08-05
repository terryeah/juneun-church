<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Support\SchemaGraph;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;

/**
 * Developer-only explorer that draws the live database schema as an
 * interactive 3D force-directed graph.
 *
 * Tables become nodes and real foreign key constraints become edges, so
 * the picture is read from the connection on every cache miss and never
 * has to be maintained by hand.
 */
class DatabaseGraph extends Page
{
    protected string $view = 'filament.pages.database-graph';

    protected static ?string $slug = 'database-graph';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCircleStack;

    protected static ?string $navigationLabel = '데이터베이스';

    protected static ?string $title = '데이터베이스';

    protected static ?int $navigationSort = 14;

    /**
     * Restricted to the developer role, matching the activity log. A
     * super admin without that role is refused.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('developer') ?? false;
    }

    /**
     * The schema graph handed to the browser as JSON.
     *
     * @return array<string, mixed>
     */
    #[Computed]
    public function graph(): array
    {
        return SchemaGraph::cached();
    }

    /**
     * Drops the cached graph so a schema change shows up immediately
     * rather than after the cache window. The canvas is held outside
     * Livewire's reach with wire:ignore, so the page is reloaded to
     * hand the browser the freshly read payload.
     *
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('다시 읽기')
                ->icon(Heroicon::OutlinedArrowPath)
                ->action(function (): void {
                    Cache::forget(SchemaGraph::CACHE_KEY);

                    Notification::make()
                        ->title('스키마를 다시 읽었습니다.')
                        ->success()
                        ->send();

                    $this->redirect(static::getUrl());
                }),
        ];
    }
}
