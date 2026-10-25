<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 기도제목 and 성경 필사: the two 성도 activities the pastor asked for
 * beside 큐티.
 *
 * Nothing here takes a permission. Like 묵상, a 기도제목 is read on the
 * site by the people it was shared with and by nobody in the panel,
 * and a typed verse is only a tally. The 개역한글 text is loaded here
 * rather than by a seeder so that deploy.sh, which runs migrate alone,
 * leaves the server with the verses in place.
 */
return new class extends Migration
{
    /**
     * Create the three tables and load the 31,101 verses.
     */
    public function up(): void
    {
        Schema::create('prayer_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('scope', 10);
            $table->text('body');
            $table->boolean('is_answered')->default(false);
            $table->timestamps();
            $table->index(['scope', 'created_at']);
        });

        Schema::create('bible_verses', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('book');
            $table->unsignedSmallInteger('chapter');
            $table->unsignedSmallInteger('verse');
            $table->text('text');
            $table->unique(['book', 'chapter', 'verse']);
        });

        Schema::create('bible_typings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bible_verse_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'bible_verse_id']);
            $table->index(['user_id', 'created_at']);
        });

        $this->loadVerses();
    }

    /**
     * Drop the three tables.
     */
    public function down(): void
    {
        Schema::dropIfExists('bible_typings');
        Schema::dropIfExists('bible_verses');
        Schema::dropIfExists('prayer_requests');
    }

    /**
     * Read 성경전서 개역한글판 (대한성서공회, public domain) from the
     * compressed file in database/data and insert it in chunks.
     */
    private function loadVerses(): void
    {
        $handle = gzopen(database_path('data/krv.jsonl.gz'), 'rb');
        $rows = [];

        while (($line = gzgets($handle)) !== false) {
            $row = json_decode($line, true);

            if (! $row) {
                continue;
            }

            $rows[] = ['book' => $row['b'], 'chapter' => $row['c'], 'verse' => $row['v'], 'text' => $row['t']];

            if (count($rows) === 500) {
                DB::table('bible_verses')->insert($rows);
                $rows = [];
            }
        }

        gzclose($handle);

        if ($rows) {
            DB::table('bible_verses')->insert($rows);
        }
    }
};
