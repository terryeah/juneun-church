<?php

namespace Tests\Feature;

use App\Filament\Resources\Photos\Pages\ListPhotos;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Picking photographs for the home band from the 사진 listing.
 *
 * Choosing ten out of three thousand one at a time was the job this
 * replaces, so the interesting case is not that the action works but
 * that the ten-photo ceiling still holds when a whole album is
 * selected at once.
 */
class SliderBulkActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed roles and permissions, then return a super admin.
     */
    private function admin(): User
    {
        $this->seed(RoleSeeder::class);

        foreach (Filament::getPanel('admin')->getResources() as $resource) {
            $model = class_basename($resource::getModel());

            foreach (['ViewAny', 'View', 'Create', 'Update', 'Delete'] as $prefix) {
                Permission::findOrCreate("{$prefix}:{$model}", 'web');
            }
        }

        $this->seed(RolePermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        return $admin;
    }

    /**
     * A selection that fits goes onto the band in one action.
     */
    public function test_a_selection_that_fits_is_added(): void
    {
        $photos = Photo::factory()->count(4)->for(Album::factory()->create())->create();

        Livewire::actingAs($this->admin())
            ->test(ListPhotos::class)
            ->callTableBulkAction('addToSlider', $photos);

        $this->assertSame(4, Photo::query()->where('featured_in_slider', true)->count());
    }

    /**
     * A selection that would overflow the band changes nothing at all.
     *
     * Taking the first few and dropping the rest would put pictures on
     * the front page the editor did not choose, so the action refuses
     * whole and says how many places are left.
     */
    public function test_a_selection_that_overflows_is_refused_whole(): void
    {
        $album = Album::factory()->create();
        Photo::factory()->count(8)->for($album)->create(['featured_in_slider' => true]);
        $more = Photo::factory()->count(5)->for($album)->create();

        Livewire::actingAs($this->admin())
            ->test(ListPhotos::class)
            ->callTableBulkAction('addToSlider', $more);

        $this->assertSame(8, Photo::query()->where('featured_in_slider', true)->count());
        $this->assertSame(0, $more->filter->fresh()->where('featured_in_slider', true)->count());
    }

    /**
     * Photographs already on the band do not consume a second place.
     */
    public function test_photos_already_on_the_band_are_not_counted_twice(): void
    {
        $album = Album::factory()->create();
        $picked = Photo::factory()->count(9)->for($album)->create(['featured_in_slider' => true]);
        $fresh = Photo::factory()->for($album)->create();

        Livewire::actingAs($this->admin())
            ->test(ListPhotos::class)
            ->callTableBulkAction('addToSlider', $picked->concat([$fresh]));

        $this->assertSame(Photo::SLIDER_LIMIT, Photo::query()->where('featured_in_slider', true)->count());
    }

    /**
     * Taking photographs back off the band is the same one action.
     */
    public function test_a_selection_is_removed(): void
    {
        $photos = Photo::factory()->count(3)->for(Album::factory()->create())->create([
            'featured_in_slider' => true,
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListPhotos::class)
            ->callTableBulkAction('removeFromSlider', $photos);

        $this->assertSame(0, Photo::query()->where('featured_in_slider', true)->count());
    }
}
