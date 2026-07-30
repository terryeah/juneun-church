<?php

namespace App\Filament\Resources\Photos\Concerns;

use App\Models\Photo;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

/**
 * Enforces the ten-photo limit for the home slider on save.
 *
 * When an eleventh photo is about to be ticked, the save is halted
 * and a popup lists the ten already-picked photos so the editor can
 * decide which one to untick first.
 */
trait LimitsSliderPicks
{
    /**
     * Halt with an explanatory popup when the slider is already full.
     *
     * @param  array<string, mixed>  $data
     */
    protected function guardSliderLimit(array $data, ?Photo $ignore = null): void
    {
        if (! ($data['featured_in_slider'] ?? false)) {
            return;
        }

        $picked = Photo::query()
            ->where('featured_in_slider', true)
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->getKey()))
            ->orderByDesc('created_at')
            ->get();

        if ($picked->count() < 10) {
            return;
        }

        $rows = $picked
            ->map(fn (Photo $photo): string => sprintf(
                '&#10003; %s · %s · %s',
                e($photo->filename),
                $photo->created_at?->format('Y-m-d'),
                number_format(($photo->file_size ?? 0) / 1048576, 2).' MB',
            ))
            ->implode('<br>');

        Notification::make()
            ->danger()
            ->title('홈 슬라이더가 가득 찼습니다')
            ->body(new HtmlString('슬라이더에는 최대 10장까지만 넣을 수 있습니다.<br>아래에서 하나를 해제한 뒤 다시 저장해 주세요.<br><br>'.$rows))
            ->persistent()
            ->send();

        $this->halt();
    }
}
