<?php

namespace App\Filament\Resources\Photos\Pages;

use App\Filament\Resources\Photos\Concerns\LimitsSliderPicks;
use App\Filament\Resources\Photos\PhotoResource;
use App\Filament\Support\SaveUploadsAsWebp;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

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
        if ($this->record->thumbnail_path !== null) {
            return;
        }

        $disk = Storage::disk(config('filesystems.media'));
        $thumbnail = SaveUploadsAsWebp::thumbnail((string) $disk->get($this->record->path));

        if ($thumbnail !== null) {
            $thumbnailPath = dirname($this->record->path).'/thumbs/'.basename($this->record->path);
            $disk->put($thumbnailPath, $thumbnail);
            $this->record->forceFill(['thumbnail_path' => $thumbnailPath])->saveQuietly();
        }
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
