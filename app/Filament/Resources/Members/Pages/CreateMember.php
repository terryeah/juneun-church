<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\Concerns\ManagesSiteAccount;
use App\Filament\Resources\Members\MemberResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMember extends CreateRecord
{
    protected static bool $canCreateAnother = false;

    use ManagesSiteAccount;

    protected static string $resource = MemberResource::class;

    /**
     * Sync the linked site account after the roster record is saved.
     */
    protected function afterCreate(): void
    {
        $this->syncSiteAccount();
    }
}
