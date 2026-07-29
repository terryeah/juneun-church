<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Shifts every stored datetime value from UTC to Australia/Brisbane.
 *
 * The application timezone changed from UTC to Australia/Brisbane, so
 * timestamps written before the change move forward ten hours to keep
 * their real-world meaning. Date-only columns (event dates, sermon
 * dates, snapshot dates) are untouched because they were always
 * entered as local dates.
 */
return new class extends Migration
{
    /**
     * Datetime columns to shift, keyed by table.
     *
     * @var array<string, array<string>>
     */
    private array $columns = [
        'activity_log' => ['created_at', 'updated_at'],
        'albums' => ['created_at', 'updated_at'],
        'analytics_snapshots' => ['created_at', 'updated_at'],
        'announcements' => ['published_at', 'expires_at', 'created_at', 'updated_at'],
        'bulletins' => ['created_at', 'updated_at'],
        'events' => ['created_at', 'updated_at'],
        'permissions' => ['created_at', 'updated_at'],
        'photos' => ['created_at', 'updated_at'],
        'positions' => ['created_at', 'updated_at'],
        'roles' => ['created_at', 'updated_at'],
        'sermons' => ['created_at', 'updated_at'],
        'service_types' => ['created_at', 'updated_at'],
        'site_settings' => ['created_at', 'updated_at'],
        'staff_members' => ['created_at', 'updated_at'],
        'users' => ['email_verified_at', 'created_at', 'updated_at'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->shift(10);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->shift(-10);
    }

    /**
     * Move every configured column by the given number of hours.
     *
     * Only the MySQL environments hold pre-change data; the SQLite
     * test database is always created fresh, so it is skipped.
     */
    private function shift(int $hours): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->columns as $table => $columns) {
            foreach ($columns as $column) {
                DB::statement(
                    "UPDATE `{$table}` SET `{$column}` = DATE_ADD(`{$column}`, INTERVAL {$hours} HOUR) WHERE `{$column}` IS NOT NULL"
                );
            }
        }
    }
};
