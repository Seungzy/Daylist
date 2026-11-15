<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->constrained()->restrictOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('location_consent_at')->nullable();
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
        });
        Schema::create('membership_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membership_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('membership_level_id')->constrained()->restrictOnDelete();
            $table->foreignId('member_id')->nullable()->unique()->constrained()->restrictOnDelete();
            $table->string('reference_no', 50)->unique();
            $table->string('full_name', 150);
            $table->string('email');
            $table->string('phone', 30)->nullable();
            $table->string('address', 255)->nullable();
            $table->decimal('fee', 10, 2);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('location_consent_at')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('verification_queued_at')->nullable();
            $table->timestamp('verification_sent_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_applications');
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('member_id');
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn('due_date');
        });
        Schema::table('members', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->dropColumn('user_id');
            $table->dropColumn(['latitude', 'longitude', 'location_consent_at']);
        });
    }
};
