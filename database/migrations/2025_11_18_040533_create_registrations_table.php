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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('registration_type', ['individual', 'team']);
            $table->string('team_name')->nullable();
            $table->json('team_members_json')->nullable();
            $table->string('individual_name')->nullable();
            $table->boolean('payment_required')->default(false);
            $table->decimal('payment_amount', 8, 2)->default(0);
            $table->enum('payment_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->string('payment_method', 100)->nullable();
            $table->string('transaction_id')->nullable();
            $table->date('payment_date')->nullable();
            $table->text('admin_notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamps();
            
            $table->unique(['event_id', 'user_id'], 'unique_user_event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
