<?php

namespace Tests\Feature;

use App\Filament\Resources\Offerings\OfferingResource;
use App\Filament\Resources\PersonalOfferings\Pages\CreatePersonalOffering;
use App\Filament\Resources\PersonalOfferings\PersonalOfferingResource;
use App\Models\Member;
use App\Models\Offering;
use App\Models\PersonalOffering;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Forms\Components\Select;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Covers the individual giving records (개인 헌금): the create page
 * renders and submitting it stores the record against its Sunday.
 */
class PersonalOfferingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed roles, the PersonalOffering permissions and sign in as a
     * super admin.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        foreach (['ViewAny', 'View', 'Create', 'Update', 'Delete'] as $prefix) {
            Permission::findOrCreate("{$prefix}:PersonalOffering", 'web');
            Permission::findOrCreate("{$prefix}:Offering", 'web');
        }

        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->actingAs($user);
    }

    /**
     * The create page renders.
     */
    public function test_create_page_renders(): void
    {
        $this->get(PersonalOfferingResource::getUrl('create'))->assertOk();
    }

    /**
     * The offering edit page renders with its personal offering
     * relation manager.
     */
    public function test_offering_edit_page_renders_the_relation_manager(): void
    {
        $offering = Offering::create(['sunday_date' => '2026-08-02', 'items' => []]);

        $this->get(OfferingResource::getUrl('edit', ['record' => $offering]))
            ->assertOk()
            ->assertSee('개인 헌금');
    }

    /**
     * Submitting the create form stores the giving against its Sunday
     * and roster member.
     */
    public function test_super_admin_can_create_a_personal_offering(): void
    {
        $offering = Offering::create(['sunday_date' => '2026-08-02', 'items' => []]);
        $member = Member::create(['name' => '홍길동']);

        Livewire::test(CreatePersonalOffering::class)
            ->fillForm([
                'offering_id' => $offering->id,
                'member_id' => $member->id,
                'name' => '홍길동',
                'category' => '감사헌금',
                'amount' => '150.50',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $giving = PersonalOffering::sole();

        $this->assertSame($offering->id, $giving->offering_id);
        $this->assertSame($member->id, $giving->member_id);
        $this->assertSame('홍길동', $giving->name);
        $this->assertSame('150.50', $giving->amount);
    }

    /**
     * The 성도 picker looks names up on the server instead of listing
     * the roster into the page.
     *
     * 재정부 reaches this form and nothing else about the congregation,
     * so eager options handed a finance volunteer every member's name
     * and id in one request - including 별세 records and anyone kept
     * off the public page.
     */
    public function test_the_member_picker_does_not_ship_the_roster_into_the_page(): void
    {
        Member::create(['name' => '홍길동']);
        Member::create(['name' => '별세하신분', 'status' => '별세', 'is_published' => false]);

        $finance = User::factory()->create();
        $finance->assignRole('finance_officer');

        $this->actingAs($finance)
            ->get(PersonalOfferingResource::getUrl('create'))
            ->assertOk()
            ->assertDontSee('홍길동')
            ->assertDontSee('별세하신분');
    }

    /**
     * Searching still finds a member, a saved value still renders its
     * name, and picking one still fills 성함.
     */
    public function test_the_member_picker_still_searches_and_fills_the_name(): void
    {
        $member = Member::create(['name' => '홍길동']);
        Member::create(['name' => '김철수']);

        $page = Livewire::test(CreatePersonalOffering::class);

        /** The live picker on the page, re-resolved after every update. */
        $picker = fn (): Select => $page->instance()->form->getComponent(
            fn ($component): bool => $component->getStatePath() === 'data.member_id',
        );

        $this->assertSame([$member->id => '홍길동'], $picker()->getSearchResults('홍길'));
        $this->assertSame([], $picker()->getSearchResults('아무도아닌'));

        $page->set('data.member_id', $member->id)->assertFormSet(['name' => '홍길동']);

        $this->assertSame('홍길동', $picker()->getOptionLabel());
    }
}
