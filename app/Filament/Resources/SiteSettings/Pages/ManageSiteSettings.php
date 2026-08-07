<?php

namespace App\Filament\Resources\SiteSettings\Pages;

use App\Filament\Resources\SiteSettings\Schemas\SiteSettingForm;
use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;

/**
 * The whole of 사이트 설정 on a single form.
 *
 * The settings are a fixed set: the public views read each key by name,
 * so a row that can be created adds nothing and a row that can be
 * deleted quietly empties the footer, the timetable or the giving
 * account numbers. This page replaces the per-row create/edit/delete
 * flow with one form that writes every key at once, which is why the
 * resource no longer registers those pages.
 *
 * @property-read Schema $form
 */
class ManageSiteSettings extends Page
{
    protected static string $resource = SiteSettingResource::class;

    protected string $view = 'filament-panels::pages.page';

    protected static ?string $title = '사이트 설정';

    protected ?string $subheading = '공개 사이트에 표시되는 값들입니다. 각 묶음의 설명이 그 값이 어느 페이지의 어느 자리에 나타나는지 알려 줍니다.';

    /**
     * Form state, keyed by setting key.
     *
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    /**
     * Load every stored setting into the form.
     *
     * Keys with no field on the form are dropped when the state is
     * dehydrated on save, so a retired row is left untouched rather
     * than blanked.
     */
    public function mount(): void
    {
        $this->form->fill(SiteSetting::query()->pluck('value', 'key')->all());
    }

    /**
     * The form state lives under `data` and stacks in a single column
     * so each group reads as its own block.
     */
    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->statePath('data');
    }

    /**
     * The grouped settings schema.
     */
    public function form(Schema $schema): Schema
    {
        return SiteSettingForm::configure($schema);
    }

    /**
     * Write every field back to its row.
     *
     * Saving through the model rather than the query builder keeps the
     * settings cache flush and the activity log entry that the model's
     * hooks provide. A key with no row is created rather than skipped,
     * so a setting the code reads can never stay missing.
     */
    public function save(): void
    {
        abort_unless(static::canEditSettings(), 403);

        foreach ($this->form->getState() as $key => $value) {
            $setting = SiteSetting::query()->firstOrNew(['key' => $key], ['group' => 'general']);
            $setting->value = $value;
            $setting->save();
        }

        Notification::make()
            ->success()
            ->title('사이트 설정을 저장했습니다.')
            ->send();
    }

    /**
     * Whether the signed-in user may change the settings, as opposed to
     * only reading them.
     */
    public static function canEditSettings(): bool
    {
        return auth()->user()?->can('Update:SiteSetting') ?? false;
    }

    /**
     * Render the form with its save button in a sticky footer.
     */
    public function content(Schema $schema): Schema
    {
        return $schema->components([
            $this->getFormContentComponent(),
        ]);
    }

    /**
     * The submit-wrapped form, mirroring how Filament's own edit pages
     * assemble theirs.
     */
    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment($this->getFormActionsAlignment())
                    ->sticky($this->areFormActionsSticky())
                    ->key('form-actions'),
            ]);
    }

    /**
     * The save button, hidden from anyone holding read access alone.
     *
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('저장')
                ->submit('save')
                ->keyBindings(['mod+s'])
                ->visible(static::canEditSettings()),
        ];
    }
}
