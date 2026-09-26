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
        // 1. Commercial Plans
        Schema::create('plans', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('key')->unique();
            $table->json('name');
            $table->json('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('MAD');
            $table->string('billing_period')->default('yearly'); // yearly, monthly, lifetime
            $table->boolean('is_active')->default(true);
            $table->boolean('is_recommended')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        // 2. Commercial Features Catalog
        Schema::create('features', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('key')->unique();
            $table->json('name');
            $table->json('description')->nullable();
            $table->timestamps();
        });

        // 3. Plan Features Pivot (Capabilities & Quotas)
        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->foreignUlid('feature_id')->constrained('features')->cascadeOnDelete();
            $table->string('value')->nullable(); // e.g. "true", "unlimited", "5"
            $table->timestamps();

            $table->unique(['plan_id', 'feature_id']);
        });

        // 4. Payment Settings (Manual Bank Transfer, RIB, Cash instructions)
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('key')->unique();
            $table->json('title');
            $table->json('instructions')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('rib')->nullable();
            $table->string('swift_bic')->nullable();
            $table->string('account_holder')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        // 5. Payment Requests (Manual Payment Proofs & Status)
        Schema::create('payment_requests', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('MAD');
            $table->string('payment_method')->default('bank_transfer');
            $table->string('reference_number')->nullable();
            $table->text('customer_notes')->nullable();
            $table->string('status')->default('pending'); // pending, under_review, approved, rejected, cancelled
            $table->string('proof_path')->nullable();
            $table->string('proof_original_name')->nullable();
            $table->string('proof_mime')->nullable();
            $table->unsignedBigInteger('proof_size')->nullable();
            $table->foreignUlid('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });

        // 6. Subscriptions
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('plan_id')->constrained('plans')->restrictOnDelete();
            $table->foreignUlid('payment_request_id')->nullable()->constrained('payment_requests')->nullOnDelete();
            $table->string('status')->default('active'); // pending, active, expired, cancelled, rejected
            $table->decimal('price_amount', 10, 2);
            $table->string('price_currency', 3)->default('MAD');
            $table->string('billing_period')->default('yearly');
            $table->json('plan_snapshot'); // Complete immutable snapshot of plan & features at purchase
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignUlid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('payment_requests');
        Schema::dropIfExists('payment_settings');
        Schema::dropIfExists('plan_features');
        Schema::dropIfExists('features');
        Schema::dropIfExists('plans');
    }
};
