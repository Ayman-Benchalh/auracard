<?php

declare(strict_types=1);

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
        // 1. Inquiries & Leads captured via profile forms
        Schema::create('profile_leads', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message')->nullable();
            $table->json('form_data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['profile_id', 'is_read', 'created_at']);
        });

        // 2. Privacy-Conscious Profile Analytics
        Schema::create('profile_analytics_events', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->string('event_type'); // view, nfc_tap, qr_scan, vcard_download, link_click
            $table->string('visitor_hash', 64)->nullable(); // SHA-256 HMAC of IP + User-Agent + rotating salt. Raw IP is never stored.
            $table->string('target_url', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['profile_id', 'event_type', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_analytics_events');
        Schema::dropIfExists('profile_leads');
    }
};
