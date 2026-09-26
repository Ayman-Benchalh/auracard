<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'slug' => 'minimal',
                'name' => ['en' => 'Minimal Clean', 'fr' => 'Minimaliste Épuré', 'ar' => 'بسيط وراقي'],
                'description' => ['en' => 'Clean typography with maximum readability and focus on essentials.'],
                'preview_url' => '/images/templates/minimal.png',
                'is_active' => true,
                'is_premium' => false,
            ],
            [
                'slug' => 'executive',
                'name' => ['en' => 'Executive Corporate', 'fr' => 'Exécutif Professionnel', 'ar' => 'تنفيذي رسمي'],
                'description' => ['en' => 'Structured, authoritative layout designed for leaders and legal counsels.'],
                'preview_url' => '/images/templates/executive.png',
                'is_active' => true,
                'is_premium' => false,
            ],
            [
                'slug' => 'creative',
                'name' => ['en' => 'Creative Studio', 'fr' => 'Studio Créatif', 'ar' => 'استوديو إبداعي'],
                'description' => ['en' => 'Vibrant showcases with dynamic grids for portfolios and visuals.'],
                'preview_url' => '/images/templates/creative.png',
                'is_active' => true,
                'is_premium' => false,
            ],
            [
                'slug' => 'modern_compact',
                'name' => ['en' => 'Modern Compact', 'fr' => 'Moderne Compact', 'ar' => 'عصري ومدمج'],
                'description' => ['en' => 'Dense single-screen card layout optimized for rapid contact saving.'],
                'preview_url' => '/images/templates/compact.png',
                'is_active' => true,
                'is_premium' => false,
            ],
            [
                'slug' => 'grid_showcase',
                'name' => ['en' => 'Grid Showcase', 'fr' => 'Vitrine Grille', 'ar' => 'شبكة العرض'],
                'description' => ['en' => 'Bento-style multi-column layout for diverse services and links.'],
                'preview_url' => '/images/templates/grid.png',
                'is_active' => true,
                'is_premium' => false,
            ],
            [
                'slug' => 'editorial',
                'name' => ['en' => 'Editorial Serif', 'fr' => 'Éditorial Serif', 'ar' => 'كلاسيكي تحريري'],
                'description' => ['en' => 'Magazine-style narrative flow with refined serif typography.'],
                'preview_url' => '/images/templates/editorial.png',
                'is_active' => true,
                'is_premium' => false,
            ],
        ];

        foreach ($templates as $t) {
            Template::updateOrCreate(
                ['key' => $t['slug']],
                [
                    'name' => $t['name'],
                    'description' => $t['description'],
                    'preview_image' => $t['preview_url'],
                    'layout_type' => $t['slug'],
                    'is_active' => $t['is_active'],
                    'is_premium' => $t['is_premium'],
                ]
            );
        }
    }
}
