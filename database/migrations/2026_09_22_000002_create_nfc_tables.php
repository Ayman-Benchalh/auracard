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
        // 1. NFC Card Batches Table
        Schema::create('nfc_card_batches', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('batch_code', 64)->unique();
            $table->string('card_model', 64)->nullable();
            $table->string('card_color', 32)->nullable();
            $table->string('material', 64)->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->text('notes')->nullable();
            $table->foreignUlid('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 2. Physical NFC Cards Table
        Schema::create('nfc_cards', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('batch_id')->nullable()->constrained('nfc_card_batches')->nullOnDelete();
            $table->string('serial_number', 64)->unique()->index();
            $table->string('public_token_hash', 64)->unique()->index(); // SHA-256 of raw public token
            $table->string('claim_code_hash')->nullable(); // Laravel Hash::make of one-time claim code
            $table->string('status', 32)->default('inventory')->index(); // inventory, reserved, sold, unclaimed, claimed, active, suspended, lost, replaced, disabled
            $table->string('card_type', 64)->nullable();
            $table->foreignUlid('user_id')->nullable()->constrained('users')->nullOnDelete()->index();
            $table->ulid('profile_id')->nullable()->index(); // Prepared for Phase 3 Profile Engine
            $table->timestamp('sold_at')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->foreignUlid('replaced_by_card_id')->nullable()->constrained('nfc_cards')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Card Entitlements Table (Immutable Physical Hardware Bound Entitlement)
        Schema::create('card_entitlements', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('source_nfc_card_id')->index(); // Original physical hardware card (immutable)
            $table->foreign('source_nfc_card_id', 'fk_ce_source_card')->references('id')->on('nfc_cards')->restrictOnDelete();
            
            $table->ulid('current_nfc_card_id')->unique(); // Currently active card (updated on replacement)
            $table->foreign('current_nfc_card_id', 'fk_ce_current_card')->references('id')->on('nfc_cards')->restrictOnDelete();
            
            $table->ulid('user_id')->index();
            $table->foreign('user_id', 'fk_ce_user')->references('id')->on('users')->restrictOnDelete();
            
            $table->ulid('profile_id')->nullable()->index();
            $table->unsignedInteger('initial_credits_amount')->default(2);
            $table->boolean('is_initial_credits_granted')->default(false)->index();
            $table->timestamp('initial_credits_granted_at')->nullable();
            $table->string('status', 32)->default('active')->index(); // active, transferred, revoked
            $table->timestamps();
        });

        // 4. Card Claims History Table
        Schema::create('card_claims', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('nfc_card_id')->index();
            $table->foreign('nfc_card_id', 'fk_cc_card')->references('id')->on('nfc_cards')->restrictOnDelete();
            
            $table->ulid('user_id')->index();
            $table->foreign('user_id', 'fk_cc_user')->references('id')->on('users')->restrictOnDelete();
            
            $table->ulid('card_entitlement_id')->nullable()->index();
            $table->foreign('card_entitlement_id', 'fk_cc_entitlement')->references('id')->on('card_entitlements')->nullOnDelete();
            
            $table->string('visitor_identifier', 64)->nullable(); // Privacy-safe time-bounded HMAC
            $table->text('user_agent')->nullable();
            $table->timestamp('claimed_at')->index();
            $table->timestamps();
        });

        // 5. Card Transfers History Table
        Schema::create('card_transfers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('nfc_card_id')->index();
            $table->foreign('nfc_card_id', 'fk_ct_card')->references('id')->on('nfc_cards')->restrictOnDelete();
            
            $table->ulid('card_entitlement_id')->nullable()->index();
            $table->foreign('card_entitlement_id', 'fk_ct_entitlement')->references('id')->on('card_entitlements')->nullOnDelete();
            
            $table->ulid('from_user_id')->nullable()->index();
            $table->foreign('from_user_id', 'fk_ct_from_user')->references('id')->on('users')->nullOnDelete();
            
            $table->ulid('to_user_id')->index();
            $table->foreign('to_user_id', 'fk_ct_to_user')->references('id')->on('users')->restrictOnDelete();
            
            $table->ulid('authorized_by_user_id')->index();
            $table->foreign('authorized_by_user_id', 'fk_ct_auth_user')->references('id')->on('users')->restrictOnDelete();
            
            $table->text('reason')->nullable();
            $table->string('transfer_type', 64)->default('ownership_transfer')->index(); // ownership_transfer, replacement, admin_correction
            $table->timestamp('transferred_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_transfers');
        Schema::dropIfExists('card_claims');
        Schema::dropIfExists('card_entitlements');
        Schema::dropIfExists('nfc_cards');
        Schema::dropIfExists('nfc_card_batches');
    }
};
