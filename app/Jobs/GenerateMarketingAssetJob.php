<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\MarketingAsset;
use App\Services\Marketing\MarketingImageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateMarketingAssetJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public MarketingAsset $asset
    ) {}

    /**
     * Execute the job.
     */
    public function handle(MarketingImageService $imageService): void
    {
        Log::info("Starting marketing image generation job for Asset ID: {$this->asset->id}");

        $this->asset->update([
            'status' => 'generating',
        ]);

        $imageService->generate($this->asset);

        Log::info("Completed marketing image generation job for Asset ID: {$this->asset->id} with status: {$this->asset->status}");
    }
}
