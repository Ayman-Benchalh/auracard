<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\GenerateMarketingAssetJob;
use App\Models\MarketingAsset;
use App\Models\User;
use Illuminate\Console\Command;

class TestQueueDispatch extends Command
{
    protected $signature = 'app:test-queue-dispatch';
    protected $description = 'Dispatch a test marketing generation job to verify queue processing';

    public function handle(): int
    {
        $admin = User::first();
        if (! $admin) {
            $this->error('No users found in database.');
            return 1;
        }

        $asset = MarketingAsset::create([
            'type' => 'hero',
            'title' => 'Queue Verification Asset',
            'prompt' => 'A luxury smart card with glowing indigo aura on black obsidian surface.',
            'provider' => 'mock',
            'status' => 'draft',
            'created_by' => $admin->id,
        ]);

        GenerateMarketingAssetJob::dispatch($asset);

        $this->info("Asset created and job dispatched with ID: {$asset->id}");
        return 0;
    }
}
