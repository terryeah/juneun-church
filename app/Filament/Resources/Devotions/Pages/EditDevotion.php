<?php

namespace App\Filament\Resources\Devotions\Pages;

use App\Filament\Resources\Devotions\DevotionResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDevotion extends EditRecord
{
    protected static string $resource = DevotionResource::class;

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
            DeleteAction::make()
                ->modalDescription('이 날짜에 성도님들이 남긴 묵상도 함께 지워집니다.'),
        ];
    }
}
