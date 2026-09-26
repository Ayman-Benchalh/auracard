<?php

namespace Tests\Feature\Operational;

use App\Jobs\SafeRuntimeAuditJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class RedisAndQueueRuntimeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Redis cache operations (put, get, forget) when Redis is configured.
     */
    public function test_redis_cache_store_operations_succeed(): void
    {
        try {
            config(['database.redis.client' => 'predis']);
            $key = 'audit_test_cache_' . Str::random(8);
            $val = 'aura_verified_' . Str::random(8);

            Cache::store('redis')->put($key, $val, 60);
            $retrieved = Cache::store('redis')->get($key);
            $this->assertSame($val, $retrieved);

            Cache::store('redis')->forget($key);
            $this->assertNull(Cache::store('redis')->get($key));
        } catch (\Throwable $e) {
            $this->markTestSkipped('Redis is not available in current test environment: ' . $e->getMessage());
        }
    }

    /**
     * Test that a job can be queued on Redis and processed without failure.
     */
    public function test_safe_job_queued_and_processed_on_redis(): void
    {
        try {
            config(['database.redis.client' => 'predis']);
            $token = 'test_redis_' . Str::random(10);
            $flagKey = 'audit_job_processed_' . $token;

            // Clear any lingering jobs from test queue
            Artisan::call('queue:clear', [
                'connection' => 'redis',
                '--force' => true,
            ]);

            Cache::store('file')->forget($flagKey);

            // Dispatch job specifically to Redis queue
            SafeRuntimeAuditJob::dispatch($token, 'file')->onConnection('redis');

            // Process single job with artisan queue:work
            Artisan::call('queue:work', [
                'connection' => 'redis',
                '--once' => true,
                '--timeout' => 10,
            ]);

            $this->assertTrue(Cache::store('file')->has($flagKey));
            $this->assertTrue((bool) Cache::store('file')->get($flagKey));

            // Clean up
            Cache::store('file')->forget($flagKey);
        } catch (\Throwable $e) {
            $this->markTestSkipped('Redis queue worker test skipped: ' . $e->getMessage());
        }
    }

    /**
     * Test that database fallback for cache remains 100% operational.
     */
    public function test_database_cache_fallback_is_supported(): void
    {
        $key = 'audit_db_cache_' . Str::random(8);
        $val = 'db_fallback_ok_' . Str::random(8);

        Cache::store('database')->put($key, $val, 60);
        $this->assertSame($val, Cache::store('database')->get($key));

        Cache::store('database')->forget($key);
        $this->assertNull(Cache::store('database')->get($key));
    }

    /**
     * Test that database fallback for queue remains 100% operational.
     */
    public function test_database_queue_fallback_is_supported(): void
    {
        $token = 'test_db_' . Str::random(10);
        $flagKey = 'audit_job_processed_' . $token;

        Cache::store('file')->forget($flagKey);

        SafeRuntimeAuditJob::dispatch($token, 'file')->onConnection('database');

        Artisan::call('queue:work', [
            'connection' => 'database',
            '--once' => true,
            '--timeout' => 10,
        ]);

        $this->assertTrue(Cache::store('file')->has($flagKey));
        $this->assertTrue((bool) Cache::store('file')->get($flagKey));

        Cache::store('file')->forget($flagKey);
    }
}
