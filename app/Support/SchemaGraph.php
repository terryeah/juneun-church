<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Reads the live database schema and shapes it as a graph of tables
 * (nodes) and foreign key constraints (edges).
 *
 * Everything is derived from the connection at request time through the
 * Schema facade, which reads information_schema on MySQL, so the picture
 * can never drift from the real database. The result is cached briefly
 * because the row counts cost one COUNT(*) per table.
 */
final class SchemaGraph
{
    /**
     * Cache key holding the whole graph payload.
     */
    public const CACHE_KEY = 'schema-graph';

    /**
     * How long the graph stays cached, in seconds.
     */
    public const CACHE_TTL = 300;

    /**
     * Laravel's own plumbing tables. They are part of the graph but are
     * hidden until the viewer asks for them, so the application schema
     * stays readable.
     *
     * @var array<int, string>
     */
    public const PLUMBING = [
        'migrations',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'sessions',
        'password_reset_tokens',
    ];

    /**
     * Domain each known table belongs to. Anything unrecognised falls
     * back to the "other" domain, so a newly migrated table still shows
     * up rather than being silently dropped.
     *
     * @var array<string, array<int, string>>
     */
    private const DOMAIN_TABLES = [
        'community' => ['users', 'members', 'membership_requests', 'cells', 'ministries', 'positions', 'staff_members'],
        'content' => ['announcements', 'events', 'sermons', 'offerings', 'personal_offerings', 'service_types'],
        'media' => ['albums', 'photos', 'bulletins'],
        'system' => [
            'activity_log', 'analytics_snapshots', 'site_settings', 'permissions', 'roles',
            'model_has_permissions', 'model_has_roles', 'role_has_permissions',
            ...self::PLUMBING,
        ],
    ];

    /**
     * Presentation metadata per domain. The sphere colour is bright
     * enough to read against the dark canvas, while the pill colour is
     * dark enough to carry white label text in either panel theme.
     *
     * @var array<string, array{label: string, color: string, pill: string}>
     */
    public const DOMAINS = [
        'community' => ['label' => '공동체', 'color' => '#3b82f6', 'pill' => '#1d4ed8'],
        'content' => ['label' => '콘텐츠', 'color' => '#f59e0b', 'pill' => '#b45309'],
        'media' => ['label' => '미디어', 'color' => '#10b981', 'pill' => '#047857'],
        'system' => ['label' => '시스템', 'color' => '#94a3b8', 'pill' => '#475569'],
        'other' => ['label' => '기타', 'color' => '#a78bfa', 'pill' => '#6d28d9'],
    ];

    /**
     * The cached graph payload.
     *
     * @return array{nodes: array<int, array<string, mixed>>, links: array<int, array<string, mixed>>, domains: array<string, array{label: string, color: string, pill: string}>, generated_at: string}
     */
    public static function cached(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, static fn (): array => self::build());
    }

    /**
     * Build the graph straight from the connection.
     *
     * @return array{nodes: array<int, array<string, mixed>>, links: array<int, array<string, mixed>>, domains: array<string, array{label: string, color: string, pill: string}>, generated_at: string}
     */
    public static function build(): array
    {
        /** Restricting to the current schema keeps unrelated databases on the same server out of the graph */
        $names = collect(Schema::getTables(Schema::getCurrentSchemaName()))
            ->pluck('name')
            ->sort()
            ->values();

        $nodes = $names->map(static fn (string $table): array => self::node($table))->all();
        $links = $names->flatMap(static fn (string $table): array => self::links($table, $names->all()))->all();

        return [
            'nodes' => $nodes,
            'links' => $links,
            'domains' => self::DOMAINS,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * One table as a graph node, with its columns, row count and domain.
     *
     * @param  string  $table  The table name.
     * @return array<string, mixed>
     */
    private static function node(string $table): array
    {
        $foreignColumns = collect(Schema::getForeignKeys($table))
            ->flatMap(static fn (array $key): array => $key['columns'])
            ->all();

        $primaryColumns = collect(Schema::getIndexes($table))
            ->firstWhere('primary', true)['columns'] ?? [];

        $columns = collect(Schema::getColumns($table))
            ->map(static fn (array $column): array => [
                'name' => $column['name'],
                'type' => $column['type'],
                'nullable' => (bool) $column['nullable'],
                'primary' => in_array($column['name'], $primaryColumns, true),
                'foreign' => in_array($column['name'], $foreignColumns, true),
            ])
            ->all();

        return [
            'id' => $table,
            'domain' => self::domain($table),
            'system' => in_array($table, self::PLUMBING, true),
            'rows' => self::rowCount($table),
            'columnCount' => count($columns),
            'columns' => $columns,
        ];
    }

    /**
     * The foreign keys leaving a table, as graph edges. Constraints
     * pointing outside the current schema are skipped because their
     * target has no node to attach to.
     *
     * @param  string  $table  The table owning the constraints.
     * @param  array<int, string>  $known  Every table present in the graph.
     * @return array<int, array<string, mixed>>
     */
    private static function links(string $table, array $known): array
    {
        return collect(Schema::getForeignKeys($table))
            ->filter(static fn (array $key): bool => in_array($key['foreign_table'], $known, true))
            ->map(static fn (array $key): array => [
                'source' => $table,
                'target' => $key['foreign_table'],
                'columns' => $key['columns'],
                'references' => $key['foreign_columns'],
                'onDelete' => $key['on_delete'] ?? 'no action',
            ])
            ->values()
            ->all();
    }

    /**
     * The domain a table belongs to, used for colour coding.
     *
     * @param  string  $table  The table name.
     */
    private static function domain(string $table): string
    {
        foreach (self::DOMAIN_TABLES as $domain => $tables) {
            if (in_array($table, $tables, true)) {
                return $domain;
            }
        }

        return 'other';
    }

    /**
     * The number of rows in a table. A table the connection cannot read
     * (a view left behind by another application, for example) reports
     * zero rather than breaking the whole graph.
     *
     * @param  string  $table  The table name.
     */
    private static function rowCount(string $table): int
    {
        try {
            return DB::table($table)->count();
        } catch (Throwable) {
            return 0;
        }
    }
}
