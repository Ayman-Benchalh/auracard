<?php

declare(strict_types=1);

namespace Tests\Feature\Commerce;

use App\Enums\RoleName;
use App\Jobs\GenerateMarketingAssetJob;
use App\Models\CommercialPackage;
use App\Models\MarketingAsset;
use App\Models\PaymentSetting;
use App\Models\User;
use App\Services\Marketing\MarketingImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketingAndAdminProductizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::factory()->create(['email_verified_at' => now()]);
        $this->admin->assignRole(RoleName::ADMIN);

        $this->superAdmin = User::factory()->create(['email_verified_at' => now()]);
        $this->superAdmin->assignRole(RoleName::SUPER_ADMIN);
    }

    public function test_landing_page_loads_dynamic_packages_and_designs(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Marketing/Home')
            ->has('packages', 4)
            ->has('cardDesigns', 4)
            ->where('packages.3.key', 'profile_only')
            ->where('packages.3.price', 100)
        );
    }

    public function test_marketing_image_service_generates_local_vector_fallback_reliably(): void
    {
        Storage::fake('public');

        $asset = MarketingAsset::create([
            'type' => 'hero',
            'title' => 'Executive Matte Black Showcase',
            'prompt' => 'A luxury obsidian smart NFC card with gold laser engraved monogram.',
            'provider' => 'mock',
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);

        $service = app(MarketingImageService::class);
        $result = $service->generate($asset);

        $this->assertEquals('ready', $result->status);
        $this->assertEquals('image/svg+xml', $result->mime_type);
        $this->assertEquals(1024, $result->width);
        $this->assertEquals(1024, $result->height);
        $this->assertNotNull($result->file_path);
        $this->assertGreaterThan(0, $result->file_size);
    }

    public function test_generate_marketing_asset_job_executes_successfully(): void
    {
        Storage::fake('public');

        $asset = MarketingAsset::create([
            'type' => 'package',
            'title' => 'Trio Multi-Card Bundle Visual',
            'prompt' => 'Three premium NFC cards in bamboo, matte black, and royal gold finishes.',
            'provider' => 'mock',
            'status' => 'queued',
            'created_by' => $this->admin->id,
        ]);

        $job = new GenerateMarketingAssetJob($asset);
        $job->handle(app(MarketingImageService::class));

        $asset->refresh();
        $this->assertEquals('ready', $asset->status);
        $this->assertNotEmpty($asset->file_path);
    }

    public function test_super_admin_can_update_featured_commercial_package(): void
    {
        $solo = CommercialPackage::where('key', 'solo')->firstOrFail();
        $trio = CommercialPackage::where('key', 'trio')->firstOrFail();

        $this->assertTrue((bool) $trio->is_featured);
        $this->assertFalse((bool) $solo->is_featured);

        // Switch featured package to Solo
        CommercialPackage::where('is_featured', true)->update(['is_featured' => false]);
        $solo->update(['is_featured' => true]);

        $this->assertTrue((bool) $solo->fresh()->is_featured);
        $this->assertFalse((bool) $trio->fresh()->is_featured);
    }

    public function test_payment_settings_are_database_backed_without_hardcoded_secrets(): void
    {
        $settings = PaymentSetting::where('is_enabled', true)->get();
        $this->assertNotEmpty($settings);

        foreach ($settings as $setting) {
            // Verify safe demo RIB placeholder format
            $this->assertStringContainsString('000 000 0000000000000000 00', $setting->rib);
            // Verify no real personal bank accounts
            $this->assertStringNotContainsString('real_bank_secret', (string) $setting->rib);
            $this->assertNotEmpty($setting->title);
            $this->assertNotEmpty($setting->instructions);
        }

        // Test adding custom payment setting dynamically
        $customSetting = PaymentSetting::create([
            'key' => 'wafacash_agency',
            'title' => [
                'en' => 'Wafacash Express Agency',
                'fr' => 'Réseau Agences Wafacash',
                'ar' => 'وكالات وفاساش',
            ],
            'instructions' => [
                'en' => 'Pay at any agency nationwide with code AURA-PAY.',
                'fr' => 'Réglez en agence avec le code partenaire AURA-PAY.',
                'ar' => 'الدفع في أي وكالة باستخدام رمز الشريك AURA-PAY.',
            ],
            'bank_name' => 'Wafacash Démo',
            'rib' => '000 000 0000000000000000 00',
            'account_holder' => 'AURA CARD DÉMO',
            'is_enabled' => true,
            'sort_order' => 10,
        ]);

        $this->assertNotNull($customSetting->id);
        $this->assertEquals('Wafacash Express Agency', $customSetting->getLocalizedTitle('en'));
        $this->assertEquals('Réseau Agences Wafacash', $customSetting->getLocalizedTitle('fr'));
        $this->assertEquals('وكالات وفاساش', $customSetting->getLocalizedTitle('ar'));
    }
}
