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
        Schema::create('registration_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('registrations')->onDelete('cascade');
            $table->foreignId('admin_user_id')->constrained('users')->onDelete('cascade');
            $table->string('action'); // payment_approved, payment_rejected, registration_approved, etc.
            $table->text('notes')->nullable();
            $table->json('old_values')->nullable(); // Store previous values for comparison
            $table->json('new_values')->nullable(); // Store new values for comparison
            $table->timestamp('created_at');
            
            $table->index(['registration_id', 'created_at']);
            $table->index(['admin_user_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_audit_logs');
    }
};
