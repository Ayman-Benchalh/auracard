<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Core catalog and security seeders (Always executed)
        $this->call([
            RolePermissionSeeder::class,
            ProfessionTypeSeeder::class,
            TemplateSeeder::class,
            ThemeSeeder::class,
            CommercialSeeder::class,
            CommercialPackageSeeder::class,
        ]);

        // 2. Development baseline demo data (Never executed in production)
        if (! app()->isProduction()) {
            $this->call([
                NfcCardDemoSeeder::class,
                ProfileDemoSeeder::class,
            ]);
        }
    }
}
