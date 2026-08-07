<?php

namespace App\Filament\Resources\Offerings\Pages;

use App\Filament\Resources\Offerings\OfferingResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOffering extends EditRecord
{
    protected static string $resource = OfferingResource::class;

    /**
     * Show the form and the related records as tabs rather than
     * stacking them down the page.
     *
     * Stacked, the save and delete buttons sit at the foot of the form
     * with the related list below them, which reads as a row of
     * buttons stranded in the middle of the page. One tab at a time
     * puts them back at the bottom of what is on screen.
     */
    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    /**
     * Label for the tab holding the form itself.
     */
    public function getContentTabLabel(): ?string
    {
        return '헌금 정보';
    }

    /**
     * Keep the delete action at the bottom of the form, next to 취소,
     * instead of in the page header.
     *
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            ...parent::getFormActions(),
            DeleteAction::make(),
        ];
    }
}
