<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class SafeRuntimeAuditJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $token,
        public ?string $cacheStore = null
    ) {}

    public function handle(): void
    {
        $store = $this->cacheStore ?? 'file';
        Cache::store($store)->put('audit_job_processed_' . $this->token, true, 300);
    }
}
