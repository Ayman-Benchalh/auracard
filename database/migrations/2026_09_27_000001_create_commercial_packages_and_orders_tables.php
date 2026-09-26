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
        // 1. Add hardware origin to physical NFC cards if not already present
        if (! Schema::hasColumn('nfc_cards', 'hardware_origin')) {
            Schema::table('nfc_cards', function (Blueprint $table) {
                $table->string('hardware_origin', 32)->default('auracard_inventory')->after('status')->index(); // auracard_inventory, customer_owned
            });
        }

        // 2. NFC Card Designs Catalog
        Schema::create('card_designs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('key', 64)->unique();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('material', 64)->default('recycled_pvc');
            $table->string('color', 32)->default('matte_black');
            $table->string('preview_image')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // 3. Commercial Packages (The Business Source of Truth: Solo, Trio, Business 5, Profile Only)
        Schema::create('commercial_packages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('key', 64)->unique()->index();
            $table->json('name');
            $table->json('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('MAD');
            $table->unsignedSmallInteger('duration_months')->default(12);
            $table->unsignedSmallInteger('physical_cards_included')->default(1);
            $table->unsignedSmallInteger('profiles_included')->default(1);
            $table->unsignedSmallInteger('edit_credits_per_profile')->default(3);
            $table->boolean('allows_customer_owned_card')->default(false);
            $table->boolean('requires_customer_owned_card')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('features_list')->nullable();
            $table->timestamps();
        });

        // 4. Orders (Snapshotting package data, pricing, card selections)
        Schema::create('orders', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('order_number', 64)->unique()->index();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('commercial_package_id')->constrained('commercial_packages')->restrictOnDelete();
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('MAD');
            $table->string('status', 32)->default('pending')->index(); // pending, under_review, approved, fulfilled, rejected, cancelled
            $table->json('package_snapshot'); // Immutable snapshot of package details at purchase
            $table->json('card_selections')->nullable(); // Chosen card_design keys/ids (e.g. 3 for Trio, 5 for Business 5)
            $table->json('external_card_data')->nullable(); // For profile_only package
            $table->text('customer_notes')->nullable();
            $table->timestamps();
        });

        // 5. Package Purchases (Active 12-Month Entitlement Instances)
        Schema::create('package_purchases', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('commercial_package_id')->constrained('commercial_packages')->restrictOnDelete();
            $table->string('status', 32)->default('active')->index(); // active, expired, cancelled, suspended
            $table->timestamp('starts_at')->index();
            $table->timestamp('ends_at')->index();
            $table->foreignUlid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // 6. Package Card Allocations (Individual Card Links)
        Schema::create('package_card_allocations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('package_purchase_id')->constrained('package_purchases')->cascadeOnDelete();
            $table->foreignUlid('card_design_id')->nullable()->constrained('card_designs')->nullOnDelete();
            $table->foreignUlid('nfc_card_id')->nullable()->constrained('nfc_cards')->nullOnDelete();
            $table->foreignUlid('card_entitlement_id')->nullable()->constrained('card_entitlements')->nullOnDelete();
            $table->string('status', 32)->default('pending_fulfillment')->index(); // pending_fulfillment, allocated, active
            $table->timestamps();
        });

        // 7. Package Profile Allocations (Ensuring isolated credits per profile)
        Schema::create('package_profile_allocations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('package_purchase_id')->constrained('package_purchases')->cascadeOnDelete();
            $table->foreignUlid('card_entitlement_id')->nullable()->constrained('card_entitlements')->nullOnDelete();
            $table->foreignUlid('profile_id')->nullable()->constrained('profiles')->nullOnDelete();
            $table->unsignedSmallInteger('edit_credits_allocated')->default(3);
            $table->string('status', 32)->default('unassigned')->index(); // unassigned, active, published
            $table->timestamps();
        });

        // 8. Link payment requests to orders
        if (Schema::hasTable('payment_requests') && ! Schema::hasColumn('payment_requests', 'order_id')) {
            Schema::table('payment_requests', function (Blueprint $table) {
                $table->foreignUlid('order_id')->nullable()->after('user_id')->constrained('orders')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('payment_requests', 'order_id')) {
            Schema::table('payment_requests', function (Blueprint $table) {
                $table->dropConstrainedForeignId('order_id');
            });
        }

        Schema::dropIfExists('package_profile_allocations');
        Schema::dropIfExists('package_card_allocations');
        Schema::dropIfExists('package_purchases');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('commercial_packages');
        Schema::dropIfExists('card_designs');

        if (Schema::hasColumn('nfc_cards', 'hardware_origin')) {
            Schema::table('nfc_cards', function (Blueprint $table) {
                $table->dropColumn('hardware_origin');
            });
        }
    }
};
