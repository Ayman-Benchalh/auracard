<?php

declare(strict_types=1);

namespace App\Services\Marketing;

use App\Models\MarketingAsset;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MarketingImageService
{
    /**
     * Generate or render marketing asset imagery using configured driver or luxury local vector fallback.
     */
    public function generate(MarketingAsset $asset): MarketingAsset
    {
        $driver = config('marketing.driver', 'none');
        $disk = config('marketing.disk', 'public');
        $baseFolder = config('marketing.path', 'marketing-assets');

        try {
            $imageData = null;
            $mimeType = 'image/png';
            $extension = 'png';
            $providerUsed = $driver;

            if ($driver === 'openai' && ! empty(config('marketing.openai.api_key'))) {
                $response = Http::withToken(config('marketing.openai.api_key'))
                    ->timeout(45)
                    ->post('https://api.openai.com/v1/images/generations', [
                        'model' => config('marketing.openai.model', 'dall-e-3'),
                        'prompt' => $asset->prompt,
                        'n' => 1,
                        'size' => config('marketing.openai.size', '1024x1024'),
                        'response_format' => 'b64_json',
                    ]);

                if ($response->successful() && ! empty($response->json('data.0.b64_json'))) {
                    $imageData = base64_decode((string) $response->json('data.0.b64_json'));
                } else {
                    Log::warning('OpenAI image generation failed, falling back to local visual engine: ' . $response->body());
                    $imageData = $this->generateLocalVectorFallback($asset);
                    $mimeType = 'image/svg+xml';
                    $extension = 'svg';
                    $providerUsed = 'mock_fallback';
                }
            } elseif ($driver === 'stability' && ! empty(config('marketing.stability.api_key'))) {
                $engine = config('marketing.stability.engine_id', 'stable-diffusion-xl-1024-v1-0');
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . config('marketing.stability.api_key'),
                    'Accept' => 'application/json',
                ])
                ->timeout(45)
                ->post("https://api.stability.ai/v1/generation/{$engine}/text-to-image", [
                    'text_prompts' => [['text' => $asset->prompt]],
                    'cfg_scale' => 7,
                    'height' => 1024,
                    'width' => 1024,
                    'samples' => 1,
                    'steps' => 30,
                ]);

                if ($response->successful() && ! empty($response->json('artifacts.0.base64'))) {
                    $imageData = base64_decode((string) $response->json('artifacts.0.base64'));
                } else {
                    Log::warning('Stability image generation failed, falling back to local visual engine: ' . $response->body());
                    $imageData = $this->generateLocalVectorFallback($asset);
                    $mimeType = 'image/svg+xml';
                    $extension = 'svg';
                    $providerUsed = 'mock_fallback';
                }
            } else {
                // Driver is none / mock or no keys configured: deterministic premium SVG engine
                $imageData = $this->generateLocalVectorFallback($asset);
                $mimeType = 'image/svg+xml';
                $extension = 'svg';
                $providerUsed = 'mock';
            }

            $fileName = Str::slug($asset->type . '-' . substr($asset->title, 0, 30)) . '-' . Str::random(8) . '.' . $extension;
            $filePath = $baseFolder . '/' . $fileName;

            Storage::disk($disk)->put($filePath, $imageData);

            $asset->update([
                'status' => 'ready',
                'provider' => $providerUsed,
                'file_path' => Storage::disk($disk)->url($filePath),
                'thumbnail_path' => Storage::disk($disk)->url($filePath),
                'mime_type' => $mimeType,
                'width' => 1024,
                'height' => 1024,
                'file_size' => strlen($imageData),
                'error_code' => null,
                'error_message' => null,
            ]);

            return $asset->fresh();
        } catch (\Throwable $e) {
            Log::error('Marketing image generation error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            $asset->update([
                'status' => 'failed',
                'error_code' => 'GENERATION_EXCEPTION',
                'error_message' => $e->getMessage(),
            ]);

            return $asset->fresh();
        }
    }

    /**
     * Generate an ultra-clean, high-resolution vector visual for local/offline fallback.
     */
    protected function generateLocalVectorFallback(MarketingAsset $asset): string
    {
        $escapedTitle = htmlspecialchars($asset->title, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $escapedType = strtoupper(htmlspecialchars($asset->type, ENT_QUOTES | ENT_XML1, 'UTF-8'));
        $promptSnippet = htmlspecialchars(Str::limit($asset->prompt, 90), ENT_QUOTES | ENT_XML1, 'UTF-8');

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024" width="1024" height="1024">
    <defs>
        <radialGradient id="bgGlow" cx="50%" cy="40%" r="60%">
            <stop offset="0%" stop-color="#4F46E5" stop-opacity="0.35"/>
            <stop offset="60%" stop-color="#0F172A" stop-opacity="0.95"/>
            <stop offset="100%" stop-color="#020617" stop-opacity="1"/>
        </radialGradient>
        <linearGradient id="cardGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#1E293B"/>
            <stop offset="50%" stop-color="#0F172A"/>
            <stop offset="100%" stop-color="#020617"/>
        </linearGradient>
        <linearGradient id="goldAccent" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#FDE68A"/>
            <stop offset="50%" stop-color="#D97706"/>
            <stop offset="100%" stop-color="#78350F"/>
        </linearGradient>
        <linearGradient id="auraAero" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#6C5CE7"/>
            <stop offset="50%" stop-color="#818CF8"/>
            <stop offset="100%" stop-color="#06B6D4"/>
        </linearGradient>
        <filter id="neonBlur" x="-20%" y="-20%" width="140%" height="140%">
            <feGaussianBlur stdDeviation="30" result="blur"/>
            <feMerge>
                <feMergeNode in="blur"/>
                <feMergeNode in="SourceGraphic"/>
            </feMerge>
        </filter>
    </defs>

    <!-- Canvas Background -->
    <rect width="1024" height="1024" fill="url(#bgGlow)"/>

    <!-- Subtle Grid Lines -->
    <g opacity="0.08" stroke="#FFFFFF" stroke-width="1">
        <line x1="0" y1="256" x2="1024" y2="256"/>
        <line x1="0" y1="512" x2="1024" y2="512"/>
        <line x1="0" y1="768" x2="1024" y2="768"/>
        <line x1="256" y1="0" x2="256" y2="1024"/>
        <line x1="512" y1="0" x2="512" y2="1024"/>
        <line x1="768" y1="0" x2="768" y2="1024"/>
    </g>

    <!-- Glowing Aura Rings Behind Card -->
    <circle cx="512" cy="460" r="280" fill="none" stroke="url(#auraAero)" stroke-width="2" opacity="0.4" filter="url(#neonBlur)"/>
    <circle cx="512" cy="460" r="340" fill="none" stroke="#6C5CE7" stroke-width="1" stroke-dasharray="8 8" opacity="0.3"/>

    <!-- 3D Isometric Card Visual -->
    <g transform="translate(232, 230) rotate(-6, 280, 180)">
        <!-- Card Drop Shadow -->
        <rect x="0" y="20" width="560" height="350" rx="24" fill="#000000" opacity="0.6" filter="url(#neonBlur)"/>

        <!-- Card Body -->
        <rect x="0" y="0" width="560" height="350" rx="24" fill="url(#cardGrad)" stroke="url(#auraAero)" stroke-width="2"/>

        <!-- Subtle Card Chip & Antenna Tracks -->
        <rect x="50" y="50" width="64" height="50" rx="8" fill="url(#goldAccent)"/>
        <path d="M50 75 h64 M82 50 v50" stroke="#78350F" stroke-width="1.5"/>

        <!-- NFC Wireless Waves -->
        <path d="M140 60 A 25 25 0 0 1 140 90 M155 52 A 40 40 0 0 1 155 98 M170 44 A 55 55 0 0 1 170 106"
              fill="none" stroke="url(#auraAero)" stroke-width="3" stroke-linecap="round"/>

        <!-- Brand Monogram -->
        <text x="50" y="270" font-family="system-ui, sans-serif" font-weight="900" font-size="34" fill="#FFFFFF" letter-spacing="4">
            AURA CARD
        </text>
        <text x="50" y="300" font-family="system-ui, sans-serif" font-weight="500" font-size="14" fill="#94A3B8" letter-spacing="2">
            SMART NFC IDENTITY • MOROCCO
        </text>

        <!-- Decorative Chip Symbol -->
        <circle cx="490" cy="70" r="18" fill="none" stroke="#64748B" stroke-width="2"/>
        <circle cx="490" cy="70" r="8" fill="#6C5CE7"/>
    </g>

    <!-- Marketing Asset Labels & Typography -->
    <g transform="translate(512, 790)" text-anchor="middle">
        <!-- Asset Type Badge -->
        <rect x="-110" y="-36" width="220" height="32" rx="16" fill="#1E1B4B" stroke="#6C5CE7" stroke-width="1.5"/>
        <text x="0" y="-15" font-family="system-ui, sans-serif" font-weight="800" font-size="13" fill="#818CF8" letter-spacing="2">
            {$escapedType} ASSET
        </text>

        <!-- Asset Title -->
        <text x="0" y="45" font-family="system-ui, sans-serif" font-weight="800" font-size="32" fill="#FFFFFF" letter-spacing="1">
            {$escapedTitle}
        </text>

        <!-- Prompt Snippet -->
        <text x="0" y="85" font-family="system-ui, sans-serif" font-weight="400" font-size="16" fill="#94A3B8">
            {$promptSnippet}
        </text>
    </g>
</svg>
SVG;
    }
}
