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
        Schema::create('templates', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('key', 64)->unique();
            $table->json('name'); // {"en": "...", "fr": "...", "ar": "..."}
            $table->json('description')->nullable();
            $table->string('preview_image')->nullable();
            $table->string('layout_type', 64)->default('minimal');
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_premium')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->json('configuration')->nullable();
            $table->timestamps();
        });

        Schema::create('themes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('key', 64)->unique();
            $table->json('name'); // {"en": "...", "fr": "...", "ar": "..."}
            $table->json('description')->nullable();
            $table->json('default_tokens');
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_premium')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themes');
        Schema::dropIfExists('templates');
    }
};
