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
        Schema::create('profile_versions', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('profile_id')->index();
            $table->foreign('profile_id', 'fk_pv_profile')->references('id')->on('profiles')->cascadeOnDelete();

            $table->unsignedInteger('version_number');
            $table->json('content_snapshot');
            $table->json('design_snapshot');
            $table->string('content_hash', 64)->index();

            $table->ulid('published_by_user_id')->nullable()->index();
            $table->foreign('published_by_user_id', 'fk_pv_user')->references('id')->on('users')->nullOnDelete();

            $table->timestamp('published_at')->index();
            $table->timestamps();

            $table->unique(['profile_id', 'version_number'], 'uk_profile_version');
        });

        Schema::create('edit_credit_transactions', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('user_id')->index();
            $table->foreign('user_id', 'fk_ect_user')->references('id')->on('users')->restrictOnDelete();

            $table->ulid('profile_id')->nullable()->index();
            $table->foreign('profile_id', 'fk_ect_profile')->references('id')->on('profiles')->nullOnDelete();

            $table->ulid('card_entitlement_id')->nullable()->index();
            $table->foreign('card_entitlement_id', 'fk_ect_entitlement')->references('id')->on('card_entitlements')->nullOnDelete();

            $table->integer('amount'); // +2 for INITIAL_GRANT, -1 for publication
            $table->integer('balance_after');
            $table->string('reason', 64)->index(); // initial_grant, profile_publish, admin_grant, admin_correction, promotion

            $table->ulid('actor_id')->nullable()->index();
            $table->foreign('actor_id', 'fk_ect_actor')->references('id')->on('users')->nullOnDelete();

            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edit_credit_transactions');
        Schema::dropIfExists('profile_versions');
    }
};
