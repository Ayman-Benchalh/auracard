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
        Schema::create('profession_types', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('key', 64)->unique();
            $table->json('name'); // {"en": "...", "fr": "...", "ar": "..."}
            $table->json('description')->nullable();
            $table->string('icon', 64)->default('heroicon-o-briefcase');
            $table->json('default_blocks'); // Array of block types recommended for this profession
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profession_types');
    }
};
