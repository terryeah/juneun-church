<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\MemberResource;
use App\Models\Member;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMembers extends ListRecords
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    /**
     * Status tabs with live member counts.
     *
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $tabs = [
            '전체' => Tab::make('전체')->badge(Member::query()->count()),
        ];

        foreach (array_keys(MemberResource::STATUSES) as $status) {
            $tabs[$status] = Tab::make($status)
                ->badge(Member::query()->where('status', $status)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $status));
        }

        return $tabs;
    }
}
