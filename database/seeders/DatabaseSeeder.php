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
        $this->call([
            RolePermissionSeeder::class,
            NfcCardDemoSeeder::class,
            ProfessionTypeSeeder::class,
            TemplateSeeder::class,
            ThemeSeeder::class,
            ProfileDemoSeeder::class,
            CommercialSeeder::class,
            CommercialPackageSeeder::class,
        ]);
    }
}
