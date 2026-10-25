<?php

namespace App\Filament\Pages;

use App\Models\BibleTyping;
use App\Models\Cell;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

/**
 * 성경 필사 현황: who has typed how much, by 성도 and by 셀.
 *
 * The public page shows each 성도 the top ten and their own count. The
 * office wanted the whole picture - every 성도 who has written, where
 * they are up to and when they last wrote - and that belongs here,
 * with administrators, rather than on the leaderboard everyone reads.
 */
class BibleTypingReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.bible-typing-report';

    protected static ?string $slug = 'bible-typing';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static ?string $navigationLabel = '성경 필사 현황';

    protected static ?string $title = '성경 필사 현황';

    /**
     * Administrators and developers may access this page.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['developer', 'admin', 'super_admin']) ?? false;
    }

    /**
     * Every 성도 who has typed at least one verse, most prolific first.
     */
    public function table(Table $table): Table
    {
        $monthStart = now()->startOfMonth();

        return $table
            ->query(fn (): Builder => User::query()
                ->whereHas('bibleTypings')
                ->with('member.cell.leader')
                ->withCount([
                    'bibleTypings as total_count',
                    'bibleTypings as month_count' => fn (Builder $query) => $query->where('created_at', '>=', $monthStart),
                ])
                ->withMax('bibleTypings as last_typing_id', 'id')
                ->withMax('bibleTypings as last_typed_at', 'created_at'))
            ->defaultSort('total_count', 'desc')
            ->columns([
                TextColumn::make('name')->label('이름')->searchable()->sortable(),
                TextColumn::make('cell')
                    ->label('셀')
                    ->state(fn (User $record): ?string => $record->member?->cell?->displayName())
                    ->placeholder('셀 없음'),
                TextColumn::make('month_count')->label('이번 달')->numeric()->suffix('절')->sortable()->alignEnd(),
                TextColumn::make('total_count')->label('지금까지')->numeric()->suffix('절')->sortable()->alignEnd(),
                TextColumn::make('last_verse')
                    ->label('마지막 쓴 절')
                    ->state(fn (User $record): ?string => BibleTyping::query()->with('verse')->find($record->last_typing_id)?->verse->reference()),
                TextColumn::make('last_typed_at')->label('마지막 쓴 날')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('cell')
                    ->label('셀')
                    ->options(fn (): array => Cell::query()->with('leader')->orderBy('sort_order')->get()
                        ->mapWithKeys(fn (Cell $cell): array => [$cell->getKey() => $cell->displayName()])
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, $cellId) => $query->whereHas('member', fn (Builder $member) => $member->where('cell_id', $cellId)),
                    )),
            ])
            ->filtersFormColumns(1)
            ->paginated([25, 50, 100])
            ->emptyStateHeading('아직 아무도 쓰지 않았습니다');
    }

    /**
     * Each 셀's figures: how many wrote, this month's verses and the total.
     *
     * @return Collection<int, array{name: string, people: int, month: int, total: int}>
     */
    #[Computed]
    public function cells(): Collection
    {
        $monthStart = now()->startOfMonth()->toDateTimeString();

        $rows = BibleTyping::query()
            ->join('members', 'members.user_id', '=', 'bible_typings.user_id')
            ->whereNotNull('members.cell_id')
            ->groupBy('members.cell_id')
            ->get([
                'members.cell_id',
                DB::raw('count(distinct bible_typings.user_id) as people'),
                DB::raw("sum(case when bible_typings.created_at >= '{$monthStart}' then 1 else 0 end) as month"),
                DB::raw('count(*) as total'),
            ])
            ->keyBy('cell_id');

        return Cell::query()->with('leader')->findMany($rows->keys())
            ->map(fn (Cell $cell): array => [
                'name' => $cell->displayName(),
                'people' => (int) $rows[$cell->getKey()]->people,
                'month' => (int) $rows[$cell->getKey()]->month,
                'total' => (int) $rows[$cell->getKey()]->total,
            ])
            ->sortByDesc('total')
            ->values();
    }

    /**
     * The church as a whole: verses this month, verses ever, people writing.
     *
     * @return array{month: int, total: int, people: int}
     */
    #[Computed]
    public function totals(): array
    {
        return [
            'month' => BibleTyping::query()->where('created_at', '>=', now()->startOfMonth())->count(),
            'total' => BibleTyping::query()->count(),
            'people' => BibleTyping::query()->distinct('user_id')->count('user_id'),
        ];
    }
}
