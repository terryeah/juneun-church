<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\Concerns\ManagesSiteAccount;
use App\Filament\Resources\Members\MemberResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMember extends EditRecord
{
    use ManagesSiteAccount;

    protected static string $resource = MemberResource::class;

    /**
     * Sync the linked site account after the roster record is saved.
     */
    protected function afterSave(): void
    {
        $this->syncSiteAccount();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
