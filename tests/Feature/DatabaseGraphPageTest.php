<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\SchemaGraph;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Access control and schema reading tests for the developer-only
 * database graph page.
 */
class DatabaseGraphPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the application roles.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    /**
     * Developers can open the database graph.
     */
    public function test_developers_can_view_the_database_graph(): void
    {
        $developer = User::factory()->create();
        $developer->assignRole('developer');

        $this->actingAs($developer)
            ->get('/admin/database-graph')
            ->assertStatus(200)
            ->assertSee('데이터베이스');
    }

    /**
     * A super admin without the developer role is refused, matching the
     * activity log.
     */
    public function test_super_admins_without_the_developer_role_are_refused(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $this->actingAs($superAdmin)
            ->get('/admin/database-graph')
            ->assertStatus(403);
    }

    /**
     * The graph is read from the live schema, so a table's real foreign
     * keys turn into edges and its columns into node detail.
     */
    public function test_the_graph_is_derived_from_the_live_schema(): void
    {
        $graph = SchemaGraph::build();

        $photos = collect($graph['nodes'])->firstWhere('id', 'photos');

        $this->assertNotNull($photos, 'The photos table is missing from the graph.');
        $this->assertContains('album_id', array_column($photos['columns'], 'name'));
        $this->assertSame('media', $photos['domain']);

        $this->assertTrue(
            collect($graph['links'])->contains(
                fn (array $link): bool => $link['source'] === 'photos'
                    && $link['target'] === 'albums'
                    && $link['columns'] === ['album_id'],
            ),
            'The photos to albums foreign key is missing from the graph.',
        );

        $this->assertTrue(
            collect($graph['nodes'])->firstWhere('id', 'migrations')['system'],
            'Laravel plumbing tables must be flagged so they can be hidden by default.',
        );
    }
}
