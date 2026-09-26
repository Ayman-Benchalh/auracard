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
        Schema::create('profiles', function (Blueprint $table) {
            $table->ulid('id')->primary();
            
            $table->ulid('user_id')->index();
            $table->foreign('user_id', 'fk_prof_user')->references('id')->on('users')->restrictOnDelete();

            $table->ulid('card_entitlement_id')->nullable()->unique();
            $table->foreign('card_entitlement_id', 'fk_prof_entitlement')->references('id')->on('card_entitlements')->nullOnDelete();

            $table->ulid('profession_type_id')->nullable()->index();
            $table->foreign('profession_type_id', 'fk_prof_profession')->references('id')->on('profession_types')->nullOnDelete();

            $table->ulid('template_id')->nullable()->index();
            $table->foreign('template_id', 'fk_prof_template')->references('id')->on('templates')->nullOnDelete();

            $table->ulid('theme_id')->nullable()->index();
            $table->foreign('theme_id', 'fk_prof_theme')->references('id')->on('themes')->nullOnDelete();

            $table->string('slug', 128)->unique()->index();
            $table->string('display_name');
            $table->string('professional_title')->nullable();
            $table->string('company_name')->nullable();
            $table->text('bio')->nullable();
            $table->string('status', 32)->default('draft')->index(); // draft, published, suspended, archived

            $table->json('draft_design_tokens')->nullable();
            $table->json('published_design_tokens')->nullable();

            $table->timestamp('first_published_at')->nullable()->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->unsignedInteger('current_version')->default(0);

            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->boolean('is_indexable')->default(true)->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
