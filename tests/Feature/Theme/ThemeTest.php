<?php

declare(strict_types=1);

namespace Tests\Feature\Theme;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_root_layout_includes_theme_bootstrap_script_with_light_default(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $content = $response->getContent();

        // Must include theme bootstrap script
        $this->assertStringContainsString('aura-theme', $content);
        $this->assertStringContainsString("localStorage.getItem('aura-theme')", $content);
        $this->assertStringContainsString("classList.add('dark')", $content);
        $this->assertStringContainsString("classList.remove('dark')", $content);
        $this->assertStringContainsString("localStorage.setItem('aura-theme', 'light')", $content);

        // Must include default light background & dark variant tokens
        $this->assertStringContainsString('bg-[#F7F8FC]', $content);
        $this->assertStringContainsString('dark:bg-[#0B1020]', $content);
    }

    public function test_authenticated_dashboard_serves_clean_layout_with_theme_support(): void
    {
        $user = User::where('email', 'customer@auracard.ma')->firstOrFail();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('bg-[#F7F8FC]', $content);
        $this->assertStringContainsString('dark:bg-[#0B1020]', $content);
        $this->assertStringContainsString('aura-theme', $content);
    }

    public function test_public_profile_page_serves_profile_view(): void
    {
        $profile = Profile::where('slug', 'ayman-el-amrani')->firstOrFail();

        $response = $this->get("/p/{$profile->slug}");

        $response->assertStatus(200);
        $content = $response->getContent();

        // Public profile is served with its snapshot and theme data
        $this->assertStringContainsString('ayman-el-amrani', $content);
    }
}
