<?php

namespace Tests\Feature\Operational;

use App\Models\NfcCard;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\NfcCardDemoSeeder;
use Database\Seeders\ProfileDemoSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoDataProductionGuardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that production environment strictly blocks creation of demo users and predictable passwords.
     */
    public function test_production_environment_blocks_demo_users_and_cards(): void
    {
        // Mock production environment
        $this->app->detectEnvironment(fn () => 'production');
        $this->assertTrue($this->app->isProduction());

        // Run full database seeder in production mode with --force
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true]);

        // Assert demo users were NOT created
        $this->assertFalse(User::where('email', 'superadmin@auracard.ma')->exists());
        $this->assertFalse(User::where('email', 'customer@auracard.ma')->exists());
        $this->assertFalse(User::where('email', 'admin@auracard.ma')->exists());
        $this->assertFalse(User::where('email', 'support@auracard.ma')->exists());

        // Assert demo NFC cards were NOT created
        $this->assertFalse(NfcCard::where('serial_number', 'AC-DEMO-0001')->exists());
        $this->assertFalse(NfcCard::where('serial_number', 'AC-DEMO-0003')->exists());

        // Run individual demo seeders directly in production mode to test secondary defense layer
        $this->artisan('db:seed', ['--class' => RolePermissionSeeder::class, '--force' => true]);
        $this->artisan('db:seed', ['--class' => NfcCardDemoSeeder::class, '--force' => true]);
        $this->artisan('db:seed', ['--class' => ProfileDemoSeeder::class, '--force' => true]);

        // Assert secondary defense held
        $this->assertFalse(User::where('email', 'superadmin@auracard.ma')->exists());
        $this->assertFalse(User::where('email', 'customer@auracard.ma')->exists());
        $this->assertFalse(NfcCard::where('serial_number', 'AC-DEMO-0001')->exists());
    }
}
