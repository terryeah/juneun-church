<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Renders the list and create page of every admin panel resource as a
 * super admin. Catches 500s from broken form or table schemas (for
 * example a missing component import) that no focused test would hit.
 *
 * Resources are discovered from the panel at runtime so the test stays
 * green as resources are added or removed.
 */
class AdminPagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The super admin used to visit every page.
     */
    private User $superAdmin;

    /**
     * Seed roles, generate Shield-style permissions for every panel
     * resource model and sign in as a super admin. The developer role
     * is added too because the activity log is developer-only.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $prefixes = [
            'ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny',
            'ForceDelete', 'ForceDeleteAny', 'Restore', 'RestoreAny',
            'Replicate', 'Reorder',
        ];

        foreach (Filament::getPanel('admin')->getResources() as $resource) {
            $model = class_basename($resource::getModel());

            foreach ($prefixes as $prefix) {
                Permission::findOrCreate("{$prefix}:{$model}", 'web');
            }
        }

        $this->seed(RolePermissionSeeder::class);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin', 'developer');
    }

    /**
     * Every resource's index page renders.
     */
    public function test_every_resource_list_page_renders(): void
    {
        foreach (Filament::getPanel('admin')->getResources() as $resource) {
            $status = $this->actingAs($this->superAdmin)
                ->get($resource::getUrl('index'))
                ->status();

            $this->assertSame(200, $status, "List page of {$resource} returned {$status}.");
        }
    }

    /**
     * Every resource's create page renders. Read-only resources and
     * those without a create route (for example Users and StaffMembers)
     * are skipped.
     */
    public function test_every_resource_create_page_renders(): void
    {
        foreach (Filament::getPanel('admin')->getResources() as $resource) {
            if (! array_key_exists('create', $resource::getPages())) {
                continue;
            }

            $status = $this->actingAs($this->superAdmin)
                ->get($resource::getUrl('create'))
                ->status();

            $this->assertSame(200, $status, "Create page of {$resource} returned {$status}.");
        }
    }
}
