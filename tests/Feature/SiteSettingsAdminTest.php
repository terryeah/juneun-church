<?php

namespace Tests\Feature;

use App\Filament\Resources\SiteSettings\Pages\ManageSiteSettings;
use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * Covers 사이트 설정 as a single grouped form.
 *
 * The load-bearing test scans the application and the Blade views for
 * every key they read, then insists each one still has a row and a
 * labelled field. A view that starts reading a new key, or a field that
 * is renamed away from its key, fails here rather than silently
 * emptying the footer or the giving account numbers.
 */
class SiteSettingsAdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the roles and the settings the public site relies on.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, SiteSettingSeeder::class]);
    }

    /**
     * Every key read by a controller, a console command or a Blade view
     * has a row in the database and a field on the settings form.
     */
    public function test_every_key_the_public_site_reads_has_a_row_and_a_field(): void
    {
        $fields = array_keys(
            Livewire::actingAs($this->settingsAdministrator())
                ->test(ManageSiteSettings::class)
                ->assertSuccessful()
                ->instance()
                ->getSchema('form')
                ->getFlatFields()
        );

        $stored = SiteSetting::query()->pluck('key')->all();

        foreach ($this->consumedKeys() as $key) {
            $this->assertContains($key, $stored, "사이트 설정 holds no row for {$key}, which the site reads.");
            $this->assertContains($key, $fields, "{$key} is read by the site but has no field on 사이트 설정.");
        }
    }

    /**
     * Saving writes every field back to its own row, leaves the keys it
     * did not touch alone, and clears the settings cache so the public
     * pages read the new value immediately.
     */
    public function test_saving_writes_every_key_and_leaves_retired_rows_alone(): void
    {
        $retired = SiteSetting::query()->create([
            'key' => 'home_meal_photo',
            'value' => 'keep-me.webp',
            'group' => 'home',
        ]);

        Livewire::actingAs($this->settingsAdministrator())
            ->test(ManageSiteSettings::class)
            ->set('data.contact_phone', '0400 000 000')
            ->set('data.giving_bsb', '123-456')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('0400 000 000', SiteSetting::get('contact_phone'));
        $this->assertSame('123-456', SiteSetting::get('giving_bsb'));
        $this->assertSame('브리즈번 주는교회', SiteSetting::get('church_name'));
        $this->assertSame('keep-me.webp', $retired->fresh()->value);
    }

    /**
     * The phone field accepts the form its own placeholder shows.
     *
     * The footer splits this value into a dialable number and a trailing
     * label, and the field asks for exactly that, but the stock tel
     * pattern allowed digits and spacing alone - so the example the
     * placeholder offered was refused, and with it every other change
     * on the page.
     */
    public function test_the_phone_accepts_a_number_with_a_trailing_label(): void
    {
        Livewire::actingAs($this->settingsAdministrator())
            ->test(ManageSiteSettings::class)
            ->set('data.contact_phone', '0415 346 455 (담임목사)')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('0415 346 455 (담임목사)', SiteSetting::get('contact_phone'));
    }

    /**
     * Something that is not a number at all is still refused, so the
     * footer never renders a tel: link built from prose.
     */
    public function test_the_phone_refuses_a_value_that_is_not_a_number(): void
    {
        Livewire::actingAs($this->settingsAdministrator())
            ->test(ManageSiteSettings::class)
            ->set('data.contact_phone', '담임목사에게 문의')
            ->call('save')
            ->assertHasErrors('data.contact_phone');
    }

    /**
     * The giving fields refuse a BSB that is not six digits, so a typo
     * in the one value that moves money never reaches /giving.
     */
    public function test_a_malformed_bsb_is_refused(): void
    {
        Livewire::actingAs($this->settingsAdministrator())
            ->test(ManageSiteSettings::class)
            ->set('data.giving_bsb', '34069')
            ->call('save')
            ->assertHasErrors('data.giving_bsb');

        $this->assertSame('034069', SiteSetting::get('giving_bsb'));
    }

    /**
     * A setting cannot be created, renamed or deleted one row at a time
     * any more: the resource registers the single page and nothing else.
     */
    public function test_the_resource_registers_only_the_single_settings_page(): void
    {
        $this->assertSame(['index'], array_keys(SiteSettingResource::getPages()));
    }

    /**
     * Read access alone leaves the page visible and the save refused.
     */
    public function test_saving_needs_the_update_permission(): void
    {
        $reader = $this->userWithPermissions(['ViewAny:SiteSetting']);

        $this->actingAs($reader);
        $this->assertFalse(ManageSiteSettings::canEditSettings());

        Livewire::actingAs($reader)
            ->test(ManageSiteSettings::class)
            ->assertSuccessful()
            ->call('save')
            ->assertForbidden();

        $this->actingAs($this->settingsAdministrator());
        $this->assertTrue(ManageSiteSettings::canEditSettings());
    }

    /**
     * Every setting key read anywhere in the application or the views.
     *
     * The home hero is added by hand because its key reaches
     * SiteSetting::get() through a variable in HomeController rather
     * than as a literal.
     *
     * @return array<int, string>
     */
    private function consumedKeys(): array
    {
        $keys = ['home_hero_photo'];

        $files = (new Finder)
            ->files()
            ->in([base_path('app'), base_path('resources/views')])
            ->name(['*.php']);

        foreach ($files as $file) {
            preg_match_all("/SiteSetting::get\(\s*'([a-z0-9_]+)'/", $file->getContents(), $matches);

            $keys = [...$keys, ...$matches[1]];
        }

        return array_values(array_unique($keys));
    }

    /**
     * A super admin holding both settings permissions.
     */
    private function settingsAdministrator(): User
    {
        return $this->userWithPermissions(['ViewAny:SiteSetting', 'Update:SiteSetting']);
    }

    /**
     * A super admin holding exactly the permissions named.
     *
     * @param  array<int, string>  $permissions
     */
    private function userWithPermissions(array $permissions): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            $user->givePermissionTo($permission);
        }

        return $user;
    }
}
