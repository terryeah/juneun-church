<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Position;
use Database\Seeders\PositionSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Guards the two things every public page has to get right: it must not
 * hand out the congregation's details, and it must not fall over on a
 * query string somebody made up.
 */
class PublicSurfaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PositionSeeder::class, SiteSettingSeeder::class]);
    }

    /**
     * 섬기는 사람들 shows only the people the church chose to publish.
     *
     * Approving a 가입 신청 registers a 성도 with is_published false, so
     * every new member arrives one dropped condition away from having
     * their photograph, phone number and birth date on a public page.
     * Nothing tested that page at all.
     */
    public function test_an_unpublished_member_is_not_on_the_public_page(): void
    {
        /** A serving position: 섬기는 사람들 leaves the lay ones out. */
        $position = Position::query()->where('name', '전도사')->firstOrFail();

        Member::factory()->create([
            'name' => '숨은사람',
            'position_id' => $position->getKey(),
            'phone' => '0400 123 456',
            'birth_date' => '1979-04-04',
            'address' => '1 Secret St, Brisbane',
            'is_published' => false,
        ]);

        Member::factory()->create([
            'name' => '드러난사람',
            'position_id' => $position->getKey(),
            'is_published' => true,
        ]);

        $response = $this->get('/people')->assertOk();

        $response->assertSee('드러난사람');
        $response->assertDontSee('숨은사람');

        /** Nor any of what the roster holds about them. */
        $response->assertDontSee('0400 123 456');
        $response->assertDontSee('1979-04-04');
        $response->assertDontSee('1 Secret St');
    }

    /**
     * An array where a string belongs is nonsense, not a 500.
     *
     * The same mistake was made twice - ?kind[]= on 앨범 and ?type[]= on
     * 자료실 - because the first fix was tested only on the page it was
     * written for. Casting an array to a string is a PHP error, which
     * the framework turns into an unauthenticated 500 on a public page.
     *
     * @return array<string, array{0: string}>
     */
    public static function pages(): array
    {
        return [
            '앨범 kind' => ['/album?kind[]=video'],
            '앨범 visibility' => ['/album?visibility[]=members'],
            '자료실 type' => ['/downloads?type[]=documents'],
            '헌금 week' => ['/giving?week[]=2026-08-02'],
            '소식 page' => ['/news?page[]=2'],
            '앨범 page' => ['/album?page[]=2'],
        ];
    }

    #[DataProvider('pages')]
    public function test_an_array_parameter_never_breaks_a_public_page(string $url): void
    {
        $this->get($url)->assertOk();
    }
}
