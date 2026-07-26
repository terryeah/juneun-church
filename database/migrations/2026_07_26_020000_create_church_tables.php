<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates every content table for the church website.
 *
 * Tables follow the project specification: announcements (교회 소식),
 * events (교회 행사), positions and staff members (섬기는 사람들),
 * service types and sermons (예배), albums and photos (갤러리),
 * bulletins (주보) and the key-value site settings store.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('event_date');
            $table->time('event_time')->nullable();
            $table->date('end_date')->nullable();
            $table->string('location');
            $table->text('description')->nullable();
            $table->boolean('is_published')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('staff_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('position_id')->constrained('positions')->cascadeOnDelete();
            $table->string('department')->nullable();
            $table->string('photo')->nullable();
            $table->text('bio')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('service_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('sermons', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('youtube_video_id');
            $table->string('preacher')->nullable();
            $table->date('sermon_date');
            $table->foreignId('service_type_id')->constrained('service_types')->cascadeOnDelete();
            $table->string('scripture_reference')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_published')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->date('event_date');
            $table->string('cover_photo_path')->nullable();
            $table->boolean('is_published')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained('albums')->cascadeOnDelete();
            $table->string('filename');
            $table->string('original_filename');
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('bulletins', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('file_path');
            $table->date('published_at');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('bulletins');
        Schema::dropIfExists('photos');
        Schema::dropIfExists('albums');
        Schema::dropIfExists('sermons');
        Schema::dropIfExists('service_types');
        Schema::dropIfExists('staff_members');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('events');
        Schema::dropIfExists('announcements');
    }
};
