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
        Schema::create('profile_blocks', function (Blueprint $table) {
            $table->ulid('id')->primary();
            
            $table->ulid('profile_id')->index();
            $table->foreign('profile_id', 'fk_pb_profile')->references('id')->on('profiles')->cascadeOnDelete();

            $table->string('type', 64)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_enabled')->default(true)->index();
            $table->json('content');
            $table->json('settings')->nullable();
            
            $table->timestamps();
        });

        Schema::create('profile_media', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('profile_id')->index();
            $table->foreign('profile_id', 'fk_pm_profile')->references('id')->on('profiles')->cascadeOnDelete();

            $table->string('collection', 64)->index(); // avatar, cover, logo, gallery, portfolio, service, documents
            $table->string('file_path');
            $table->string('disk', 32)->default('public');
            $table->string('mime_type', 128);
            $table->unsignedBigInteger('file_size');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text')->nullable();
            $table->boolean('is_private')->default(false)->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_media');
        Schema::dropIfExists('profile_blocks');
    }
};
