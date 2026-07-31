<?php

namespace App\Filament\Resources\Photos\Pages;

use App\Filament\Resources\Photos\Concerns\LimitsSliderPicks;
use App\Filament\Resources\Photos\PhotoResource;
use App\Support\SaveUploadsAsWebp;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPhoto extends EditRecord
{
    use LimitsSliderPicks;

    protected static string $resource = PhotoResource::class;

    /**
     * Enforce the home-slider limit before saving changes.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->guardSliderLimit($data, $this->record);

        if (($data['path'] ?? null) && $data['path'] !== $this->record->path) {
            $data['filename'] = basename((string) $data['path']);
            $data['thumbnail_path'] = null;
        }

        return $data;
    }

    /**
     * Regenerate the grid thumbnail after the file was replaced.
     */
    protected function afterSave(): void
    {
        if ($this->record->thumbnail_path === null) {
            $this->record->update([
                'thumbnail_path' => SaveUploadsAsWebp::thumbnail($this->record->path),
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
