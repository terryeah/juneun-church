<?php

namespace App\Filament\Resources\Photos\Tables;

use App\Filament\Support\Author;
use App\Models\Photo;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

/**
 * The 사진 listing.
 *
 * On a phone the photograph leads the card at full width instead of
 * sitting in a grid cell at thumbnail size, which put the picture -
 * the only thing anyone is scanning for here - at 44px beside three
 * wrapped lines of UUID. The filename is a machine identifier and now
 * waits behind the column menu; the edit screen has a copy button for
 * the one time it is needed.
 */
class PhotosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail_path')
                    ->label('썸네일')
                    /**
                     * Handed a finished URL rather than a disk and a
                     * path. Given a path, Filament resolves the media
                     * disk to build the address - and resolving R2
                     * constructs an S3 client, 17 MB of AWS SDK, on
                     * every render of this table including every search
                     * keystroke and every toggle. Given a URL it returns
                     * it untouched. 동영상 already worked this way.
                     */
                    ->state(fn (Photo $record): string => $record->thumbnailUrl())
                    ->imageHeight(44)
                    ->extraCellAttributes(['class' => 'stacked-span-full stacked-hide-label stacked-media']),
                TextColumn::make('album.title')
                    ->label('앨범')
                    ->searchable(),
                TextColumn::make('filename')
                    ->label('파일명')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('file_size')
                    ->label('파일 크기')
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state / 1048576, 2).' MB' : '-')
                    ->sortable()
                    ->visibleFrom('lg'),
                /**
                 * Only the chosen few are marked. Drawn as a boolean this
                 * put a red cross on all 3,199 rows, which read as 3,199
                 * things wrong rather than ten things picked.
                 *
                 * A hollow star means the box is ticked but the band will
                 * not draw it, because the album is not 활성화. Four
                 * photographs were ticked and one reached the front page,
                 * with nothing anywhere saying where the other three had
                 * gone.
                 */
                IconColumn::make('featured_in_slider')
                    ->label('홈 슬라이더')
                    ->icon(fn (Photo $record): ?Heroicon => match (true) {
                        ! $record->featured_in_slider => null,
                        (bool) $record->album?->is_published => Heroicon::Star,
                        default => Heroicon::OutlinedStar,
                    })
                    ->color(fn (Photo $record): string => $record->album?->is_published ? 'warning' : 'gray')
                    ->tooltip(fn (Photo $record): ?string => $record->featured_in_slider && ! $record->album?->is_published
                        ? '앨범이 비활성 상태라 홈 화면에 나오지 않습니다'
                        : null),
                TextColumn::make('updated_at')
                    ->label('수정일')
                    ->dateTime()
                    ->sortable()
                    ->visibleFrom('lg'),
                Author::column('uploader.name', '업로더')
                    ->visibleFrom('lg'),
                TextColumn::make('created_at')
                    ->label('생성일')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('path')
                    ->label('경로')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            /**
             * 앨범 first, because 사진 is 3,199 rows deep and every job
             * here starts by knowing which event is being looked at.
             * sort_order only means anything inside one album, so the
             * unfiltered list interleaved every album at once.
             */
            ->filters([
                SelectFilter::make('album_id')
                    ->label('앨범')
                    ->relationship('album', 'title')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('featured_in_slider')
                    ->label('홈 슬라이더')
                    ->placeholder('전체')
                    ->trueLabel('넣은 사진')
                    ->falseLabel('넣지 않은 사진'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::addToSlider(),
                    self::removeFromSlider(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make()->label('업로드'),
            ]);
    }

    /**
     * Put the selected photographs on the home band in one go.
     *
     * The band holds ten, so a selection that would overflow it is
     * refused whole rather than filled part way: taking the first few
     * of a selection and dropping the rest would put pictures on the
     * front page the editor did not choose and leave out ones they did,
     * with nothing on screen to say which way it went.
     */
    private static function addToSlider(): BulkAction
    {
        return BulkAction::make('addToSlider')
            ->label('홈 슬라이더에 넣기')
            ->icon(Heroicon::Star)
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('홈 슬라이더에 넣기')
            ->modalDescription('선택한 사진이 홈 화면 사진 띠에 올라갑니다. 홈 화면은 누구나 볼 수 있으니, 성도의 얼굴이 담긴 사진은 넣어도 괜찮은지 한 번 확인해 주세요.')
            ->modalSubmitActionLabel('넣기')
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records): void {
                $adding = $records->where('featured_in_slider', false);

                if ($adding->isEmpty()) {
                    Notification::make()
                        ->info()
                        ->title('이미 모두 들어 있습니다')
                        ->send();

                    return;
                }

                $free = Photo::SLIDER_LIMIT - Photo::query()->where('featured_in_slider', true)->count();

                if ($adding->count() > $free) {
                    Notification::make()
                        ->danger()
                        ->title('홈 슬라이더 자리가 모자랍니다')
                        ->body(sprintf(
                            '슬라이더는 최대 %d장입니다. 지금 남은 자리는 %d장인데 %d장을 고르셨습니다. 기존 사진을 뺀 뒤 다시 시도해 주세요.',
                            Photo::SLIDER_LIMIT,
                            $free,
                            $adding->count(),
                        ))
                        ->persistent()
                        ->send();

                    return;
                }

                /** Saved one by one so each change reaches 활동 기록. */
                $adding->each(fn (Photo $photo) => $photo->update(['featured_in_slider' => true]));

                /**
                 * The band draws from 활성화된 앨범 only, so a photograph
                 * in a draft album is ticked and then never appears. Said
                 * here because the alternative is an editor picking four
                 * and finding one on the front page with nothing to
                 * explain the other three.
                 */
                $hidden = $adding->filter(fn (Photo $photo): bool => ! $photo->album?->is_published);

                Notification::make()
                    ->success()
                    ->title($adding->count().'장을 홈 슬라이더에 넣었습니다')
                    ->body($hidden->isEmpty() ? null : sprintf(
                        '다만 %d장은 앨범이 비활성 상태라 홈 화면에 나오지 않습니다. 앨범에서 활성화를 켜 주세요: %s',
                        $hidden->count(),
                        $hidden->map(fn (Photo $photo): string => $photo->album?->title ?? '-')->unique()->implode(', '),
                    ))
                    ->persistent($hidden->isNotEmpty())
                    ->send();
            });
    }

    /**
     * Take the selected photographs back off the home band.
     */
    private static function removeFromSlider(): BulkAction
    {
        return BulkAction::make('removeFromSlider')
            ->label('홈 슬라이더에서 빼기')
            ->icon(Heroicon::OutlinedStar)
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading('홈 슬라이더에서 빼기')
            ->modalSubmitActionLabel('빼기')
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records): void {
                $removing = $records->where('featured_in_slider', true);

                $removing->each(fn (Photo $photo) => $photo->update(['featured_in_slider' => false]));

                Notification::make()
                    ->success()
                    ->title($removing->count().'장을 홈 슬라이더에서 뺐습니다')
                    ->send();
            });
    }
}
