<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\Concerns\ManagesSiteAccount;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Members\Schemas\MemberForm;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMember extends EditRecord
{
    use ManagesSiteAccount;

    protected static string $resource = MemberResource::class;

    /**
     * Record that a 가입 신청 was read from here.
     *
     * Opening the roster page already writes a page-visit row, but its
     * subject is null and its description is the 성도 URL - so an
     * auditor asking who has read a given applicant's submission would
     * miss every read made through this form. The row is attached to the
     * request itself so the existing filters find it.
     *
     * Written on mount rather than from the schema, which re-evaluates
     * its closures on every Livewire round trip.
     *
     * The row is written only when the form will actually show the
     * submission, which is why the question is asked of MemberForm
     * rather than answered again here: a second copy of the permission
     * check drifted from the first and recorded a 열람 for somebody the
     * section had shown nothing to.
     */
    public function mount(int|string $record): void
    {
        parent::mount($record);

        $request = MemberForm::signupRequest($this->record);

        if ($request) {
            activity('page')
                ->causedBy(auth()->user())
                ->performedOn($request)
                ->event('visited')
                ->withProperties(['ip' => request()->ip()])
                ->log('가입 신청 열람 (성도 화면)');
        }
    }

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
