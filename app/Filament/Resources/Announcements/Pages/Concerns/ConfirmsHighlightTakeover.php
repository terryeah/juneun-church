<?php

namespace App\Filament\Resources\Announcements\Pages\Concerns;

use App\Models\Announcement;
use Filament\Actions\Action;
use Filament\Support\Exceptions\Halt;

/**
 * Asks before moving the home page 하이라이트 to another 교회 소식.
 *
 * Only one announcement carries the highlight, and Announcement's saved
 * hook takes it off whoever held it before. That is silent by design, so
 * the admin panel puts a confirmation in front of it.
 *
 * The confirmation cannot hang off the save button itself: with the
 * default form wrapper both CreateRecord and EditRecord build that
 * button with ->submit(), which renders a native type="submit" button
 * wired to the form's wire:submit handler (see Filament\Actions\Action
 * ::generateButtonHtml, type: $this->canSubmitForm() ? 'submit' : ...).
 * A submit button never calls mountAction(), so the modal that
 * requiresConfirmation() configures is never opened - the closure form
 * of requiresConfirmation() would be evaluated and then ignored.
 *
 * Instead the save runs as normal and the beforeSave / beforeCreate hook
 * - which Filament fires after validation has already passed - mounts a
 * confirmation action and halts. Confirming re-runs the same save with
 * consent recorded; cancelling simply leaves the halted save abandoned.
 */
trait ConfirmsHighlightTakeover
{
    /**
     * One-shot consent recorded by the confirmation modal.
     */
    public bool $hasConfirmedHighlightTakeover = false;

    /**
     * The modal shown when the highlight is about to change hands.
     */
    public function confirmHighlightTakeoverAction(): Action
    {
        return Action::make('confirmHighlightTakeover')
            ->requiresConfirmation()
            ->modalHeading('하이라이트를 옮길까요?')
            ->modalDescription(fn (): string => sprintf(
                '지금은 "%s" 소식이 홈 화면 하이라이트입니다. 계속하면 하이라이트가 이 소식으로 옮겨집니다.',
                $this->displacedHighlight()?->title ?? '다른',
            ))
            ->modalSubmitActionLabel('하이라이트 옮기기')
            ->modalCancelActionLabel('취소')
            ->action(function (): void {
                $this->hasConfirmedHighlightTakeover = true;

                $this->{$this->getSubmitFormLivewireMethodName()}();
            });
    }

    /**
     * Stop an edit that would take the highlight off another notice.
     */
    protected function beforeSave(): void
    {
        $this->guardHighlightTakeover();
    }

    /**
     * Stop a creation that would take the highlight off another notice.
     */
    protected function beforeCreate(): void
    {
        $this->guardHighlightTakeover();
    }

    /**
     * Halt the save and raise the modal unless consent is already in.
     */
    protected function guardHighlightTakeover(): void
    {
        if ($this->hasConfirmedHighlightTakeover) {
            $this->hasConfirmedHighlightTakeover = false;

            return;
        }

        if (! $this->displacedHighlight()) {
            return;
        }

        $this->mountAction('confirmHighlightTakeover');

        throw new Halt;
    }

    /**
     * The announcement this save would take the highlight away from.
     */
    protected function displacedHighlight(): ?Announcement
    {
        if (! ($this->data['is_highlighted'] ?? false)) {
            return null;
        }

        return Announcement::query()
            ->where('is_highlighted', true)
            ->when($this->record, fn ($query) => $query->whereKeyNot($this->record->getKey()))
            ->first();
    }
}
