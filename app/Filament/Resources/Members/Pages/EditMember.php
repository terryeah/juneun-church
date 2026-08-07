<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\Concerns\ManagesSiteAccount;
use App\Filament\Resources\Members\MemberResource;
use Filament\Actions\Action;
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
                ->modalDescription(fn (): string => $this->record->user
                    ? '이 성도의 사이트 로그인 계정도 함께 삭제됩니다. 계정만 없애려면 삭제하지 말고 사이트 계정을 꺼 주세요.'
                    : '삭제한 성도는 되돌릴 수 없습니다.'),
        ];
    }
}
