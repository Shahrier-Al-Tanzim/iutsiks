<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Events table indexes
        Schema::table('events', function (Blueprint $table) {
            // Check if indexes don't already exist before adding
            if (!$this->indexExists('events', 'events_status_date_index')) {
                $table->index(['status', 'event_date'], 'events_status_date_index');
            }
            if (!$this->indexExists('events', 'events_fest_status_index')) {
                $table->index(['fest_id', 'status'], 'events_fest_status_index');
            }
            if (!$this->indexExists('events', 'events_type_status_index')) {
                $table->index(['type', 'status'], 'events_type_status_index');
            }
            if (!$this->indexExists('events', 'events_registration_type_status_index')) {
                $table->index(['registration_type', 'status'], 'events_registration_type_status_index');
            }
            if (!$this->indexExists('events', 'events_deadline_status_index')) {
                $table->index(['registration_deadline', 'status'], 'events_deadline_status_index');
            }
            if (!$this->indexExists('events', 'events_author_index')) {
                $table->index('author_id', 'events_author_index');
            }
        });

        // Registrations table indexes
        Schema::table('registrations', function (Blueprint $table) {
            if (!$this->indexExists('registrations', 'registrations_event_status_index')) {
                $table->index(['event_id', 'status'], 'registrations_event_status_index');
            }
            if (!$this->indexExists('registrations', 'registrations_user_status_index')) {
                $table->index(['user_id', 'status'], 'registrations_user_status_index');
            }
            if (!$this->indexExists('registrations', 'registrations_payment_index')) {
                $table->index(['payment_status', 'payment_required'], 'registrations_payment_index');
            }
            if (!$this->indexExists('registrations', 'registrations_type_status_index')) {
                $table->index(['registration_type', 'status'], 'registrations_type_status_index');
            }
            if (!$this->indexExists('registrations', 'registrations_date_index')) {
                $table->index('registered_at', 'registrations_date_index');
            }
        });

        // Fests table indexes
        Schema::table('fests', function (Blueprint $table) {
            if (!$this->indexExists('fests', 'fests_status_date_index')) {
                $table->index(['status', 'start_date'], 'fests_status_date_index');
            }
            if (!$this->indexExists('fests', 'fests_creator_index')) {
                $table->index('created_by', 'fests_creator_index');
            }
        });

        // Gallery images table indexes
        Schema::table('gallery_images', function (Blueprint $table) {
            if (!$this->indexExists('gallery_images', 'gallery_imageable_index')) {
                $table->index(['imageable_type', 'imageable_id'], 'gallery_imageable_index');
            }
            if (!$this->indexExists('gallery_images', 'gallery_uploader_index')) {
                $table->index('uploaded_by', 'gallery_uploader_index');
            }
            if (!$this->indexExists('gallery_images', 'gallery_created_index')) {
                $table->index('created_at', 'gallery_created_index');
            }
        });

        // Prayer times table indexes
        Schema::table('prayer_times', function (Blueprint $table) {
            if (!$this->indexExists('prayer_times', 'prayer_times_date_index')) {
                $table->index('date', 'prayer_times_date_index');
            }
            if (!$this->indexExists('prayer_times', 'prayer_times_updater_index')) {
                $table->index('updated_by', 'prayer_times_updater_index');
            }
        });

        // Blogs table indexes (only for existing columns)
        Schema::table('blogs', function (Blueprint $table) {
            if (!$this->indexExists('blogs', 'blogs_author_index')) {
                $table->index('author_id', 'blogs_author_index');
            }
            if (!$this->indexExists('blogs', 'blogs_created_index')) {
                $table->index('created_at', 'blogs_created_index');
            }
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            if (!$this->indexExists('users', 'users_role_index')) {
                $table->index('role', 'users_role_index');
            }
            if (!$this->indexExists('users', 'users_student_id_index')) {
                $table->index('student_id', 'users_student_id_index');
            }
        });
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = \DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return !empty($indexes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Events table indexes
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('events_status_date_index');
            $table->dropIndex('events_fest_status_index');
            $table->dropIndex('events_type_status_index');
            $table->dropIndex('events_registration_type_status_index');
            $table->dropIndex('events_deadline_status_index');
            $table->dropIndex('events_author_index');
        });

        // Registrations table indexes
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropIndex('registrations_event_status_index');
            $table->dropIndex('registrations_user_status_index');
            $table->dropIndex('registrations_payment_index');
            $table->dropIndex('registrations_type_status_index');
            $table->dropIndex('registrations_date_index');
        });

        // Fests table indexes
        Schema::table('fests', function (Blueprint $table) {
            $table->dropIndex('fests_status_date_index');
            $table->dropIndex('fests_creator_index');
        });

        // Gallery images table indexes
        Schema::table('gallery_images', function (Blueprint $table) {
            $table->dropIndex('gallery_imageable_index');
            $table->dropIndex('gallery_uploader_index');
            $table->dropIndex('gallery_created_index');
        });

        // Prayer times table indexes
        Schema::table('prayer_times', function (Blueprint $table) {
            $table->dropIndex('prayer_times_date_index');
            $table->dropIndex('prayer_times_updater_index');
        });

        // Blogs table indexes
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropIndex('blogs_author_index');
            $table->dropIndex('blogs_created_index');
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_index');
            $table->dropIndex('users_student_id_index');
        });
    }
};