<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $themes = [
            [
                'slug' => 'midnight_obsidian',
                'name' => ['en' => 'Midnight Obsidian', 'fr' => 'Obsidienne Minuit', 'ar' => 'أسود ليلي مذهب'],
                'tokens' => [
                    'background' => '#0b0f19',
                    'card_background' => '#111827',
                    'primary' => '#d97706',
                    'secondary' => '#f59e0b',
                    'text' => '#f9fafb',
                    'text_muted' => '#9ca3af',
                    'border' => '#1f2937',
                    'accent' => '#f59e0b',
                ],
                'is_active' => true,
                'is_premium' => false,
            ],
            [
                'slug' => 'azure_electric',
                'name' => ['en' => 'Azure Electric', 'fr' => 'Azur Électrique', 'ar' => 'أزرق سماوي عصري'],
                'tokens' => [
                    'background' => '#0f172a',
                    'card_background' => '#1e293b',
                    'primary' => '#0284c7',
                    'secondary' => '#38bdf8',
                    'text' => '#ffffff',
                    'text_muted' => '#94a3b8',
                    'border' => '#334155',
                    'accent' => '#38bdf8',
                ],
                'is_active' => true,
                'is_premium' => false,
            ],
            [
                'slug' => 'emerald_prestige',
                'name' => ['en' => 'Emerald Prestige', 'fr' => 'Émeraude Prestige', 'ar' => 'زمردي راقي'],
                'tokens' => [
                    'background' => '#064e3b',
                    'card_background' => '#065f46',
                    'primary' => '#10b981',
                    'secondary' => '#34d399',
                    'text' => '#f0fdf4',
                    'text_muted' => '#a7f3d0',
                    'border' => '#047857',
                    'accent' => '#6ee7b7',
                ],
                'is_active' => true,
                'is_premium' => false,
            ],
            [
                'slug' => 'crimson_luxe',
                'name' => ['en' => 'Crimson Luxe', 'fr' => 'Pourpre Luxe', 'ar' => 'قرمزي فخم'],
                'tokens' => [
                    'background' => '#4c0519',
                    'card_background' => '#881337',
                    'primary' => '#f43f5e',
                    'secondary' => '#fb7185',
                    'text' => '#fff1f2',
                    'text_muted' => '#fecdd3',
                    'border' => '#9f1239',
                    'accent' => '#fda4af',
                ],
                'is_active' => true,
                'is_premium' => false,
            ],
            [
                'slug' => 'nordic_frost',
                'name' => ['en' => 'Nordic Frost', 'fr' => 'Givre Nordique', 'ar' => 'جليدي هادئ'],
                'tokens' => [
                    'background' => '#f8fafc',
                    'card_background' => '#ffffff',
                    'primary' => '#0f172a',
                    'secondary' => '#0284c7',
                    'text' => '#0f172a',
                    'text_muted' => '#64748b',
                    'border' => '#e2e8f0',
                    'accent' => '#0284c7',
                ],
                'is_active' => true,
                'is_premium' => false,
            ],
            [
                'slug' => 'minimalist_mono',
                'name' => ['en' => 'Minimalist Mono', 'fr' => 'Monochrome Minimal', 'ar' => 'أحادي التباين'],
                'tokens' => [
                    'background' => '#18181b',
                    'card_background' => '#27272a',
                    'primary' => '#fafafa',
                    'secondary' => '#a1a1aa',
                    'text' => '#fafafa',
                    'text_muted' => '#a1a1aa',
                    'border' => '#3f3f46',
                    'accent' => '#ffffff',
                ],
                'is_active' => true,
                'is_premium' => false,
            ],
        ];

        foreach ($themes as $th) {
            Theme::updateOrCreate(
                ['key' => $th['slug']],
                [
                    'name' => $th['name'],
                    'default_tokens' => $th['tokens'],
                    'is_active' => $th['is_active'],
                    'is_premium' => $th['is_premium'],
                ]
            );
        }
    }
}
