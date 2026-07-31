<?php

namespace App\Filament\Resources\Photos\Pages;

use App\Filament\Resources\Photos\Concerns\LimitsSliderPicks;
use App\Filament\Resources\Photos\PhotoResource;
use App\Filament\Support\SaveUploadsAsWebp;
use App\Models\Photo;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

/**
 * Create page that fills photo metadata from the uploaded file.
 */
class CreatePhoto extends CreateRecord
{
    use LimitsSliderPicks;

    protected static string $resource = PhotoResource::class;

    /**
     * Breadcrumb label for this page.
     */
    protected static ?string $breadcrumb = '업로드';

    /**
     * Page heading shown above the form.
     */
    public function getTitle(): string
    {
        return '사진 업로드';
    }

    /**
     * Rename the submit button from the default 만들기.
     */
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('업로드');
    }

    /**
     * Rename the create-another button to match.
     */
    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()->label('계속 업로드');
    }

    /**
     * Derive filename metadata and stamp the uploading user.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->guardSliderLimit($data);

        $data['filename'] = basename((string) $data['path']);
        $data['original_filename'] = $data['original_filename'] ?? $data['filename'];
        $data['uploaded_by'] = auth()->id();
        $data['sort_order'] = (int) Photo::query()
            ->where('album_id', $data['album_id'] ?? null)
            ->max('sort_order') + 10;

        return $data;
    }

    /**
     * Generate the grid thumbnail once the photo has been stored.
     */
    protected function afterCreate(): void
    {
        $photo = $this->record;
        $disk = Storage::disk(config('filesystems.media'));

        $thumbnail = SaveUploadsAsWebp::thumbnail((string) $disk->get($photo->path));

        if ($thumbnail !== null) {
            $thumbnailPath = dirname($photo->path).'/thumbs/'.basename($photo->path);
            $disk->put($thumbnailPath, $thumbnail);
            $photo->forceFill(['thumbnail_path' => $thumbnailPath])->saveQuietly();
        }
    }
}
