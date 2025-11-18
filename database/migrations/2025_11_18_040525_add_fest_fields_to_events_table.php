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
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('fest_id')->nullable()->constrained('fests')->onDelete('cascade');
            $table->enum('type', ['quiz', 'lecture', 'donation', 'competition', 'workshop'])->default('lecture');
            $table->enum('registration_type', ['individual', 'team', 'both', 'on_spot'])->default('individual');
            $table->string('location')->nullable();
            $table->integer('max_participants')->nullable();
            $table->decimal('fee_amount', 8, 2)->default(0);
            $table->datetime('registration_deadline')->nullable();
            $table->enum('status', ['draft', 'published', 'completed'])->default('draft');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['fest_id']);
            $table->dropColumn([
                'fest_id', 'type', 'registration_type', 'location', 
                'max_participants', 'fee_amount', 'registration_deadline', 'status'
            ]);
        });
    }
};
