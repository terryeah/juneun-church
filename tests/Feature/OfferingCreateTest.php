<?php

namespace Tests\Feature;

use App\Filament\Resources\Offerings\Pages\CreateOffering;
use App\Models\Offering;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Forms\Components\Repeater;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * End-to-end test for the offering create flow (헌금 내역): filling the
 * form persists the record with its items array and creator.
 */
class OfferingCreateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed roles, the Offering permissions and sign in as a super admin.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        foreach (['ViewAny', 'View', 'Create', 'Update', 'Delete'] as $prefix) {
            Permission::findOrCreate("{$prefix}:Offering", 'web');
        }

        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->actingAs($user);
    }

    /**
     * A Sunday no bulletin seed migration records, so the unique rule
     * on sunday_date only ever sees this test's own offering.
     */
    private const SUNDAY = '2026-09-13';

    /**
     * Submitting the create form stores the offering with its items.
     */
    public function test_super_admin_can_create_an_offering(): void
    {
        $undoRepeaterFake = Repeater::fake();

        Livewire::test(CreateOffering::class)
            ->fillForm([
                'sunday_date' => self::SUNDAY,
                'note' => '광고 후 집계',
                'items' => [
                    [
                        'category' => '감사헌금',
                        'amount' => '150.50',
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $undoRepeaterFake();

        $offering = Offering::whereDate('sunday_date', self::SUNDAY)->sole();

        $this->assertSame(self::SUNDAY, $offering->sunday_date->toDateString());
        $this->assertSame('광고 후 집계', $offering->note);
        $this->assertSame(auth()->id(), $offering->created_by);
        $this->assertSame([
            [
                'category' => '감사헌금',
                'amount' => 150.5,
            ],
        ], $offering->items);
        $this->assertSame(150.5, $offering->total());
    }
}
