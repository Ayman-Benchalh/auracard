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
        Schema::create('marketing_assets', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('type', 64)->default('hero')->index(); // hero, package, profession, social, banner
            $table->string('title');
            $table->text('prompt');
            $table->string('prompt_hash', 64)->nullable()->index();
            $table->string('provider', 32)->default('mock')->index(); // openai, stability, mock, manual
            $table->string('model', 64)->nullable();
            $table->string('status', 32)->default('draft')->index(); // draft, queued, generating, ready, approved, rejected, failed
            $table->string('file_path')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->string('mime_type', 64)->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->foreignUlid('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('error_code', 64)->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_assets');
    }
};
