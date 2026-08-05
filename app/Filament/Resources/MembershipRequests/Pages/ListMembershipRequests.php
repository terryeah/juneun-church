<?php

namespace App\Filament\Resources\MembershipRequests\Pages;

use App\Filament\Resources\MembershipRequests\MembershipRequestResource;
use App\Models\MembershipRequest;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMembershipRequests extends ListRecords
{
    protected static string $resource = MembershipRequestResource::class;

    /**
     * Status tabs with live request counts.
     *
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $tabs = [
            '전체' => Tab::make('전체')->badge(MembershipRequest::query()->count()),
        ];

        foreach (array_keys(MembershipRequest::STATUSES) as $status) {
            $tabs[$status] = Tab::make($status)
                ->badge(MembershipRequest::query()->where('status', $status)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $status));
        }

        return $tabs;
    }
}
