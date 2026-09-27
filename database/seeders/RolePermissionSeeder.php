<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\PermissionGroup;
use App\Enums\RoleName;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Core Permissions grouped by domain
        $permissions = [
            // Users
            ['name' => 'users.view', 'group' => PermissionGroup::USERS, 'display_name' => ['en' => 'View Users', 'fr' => 'Voir Utilisateurs', 'ar' => 'عرض المستخدمين']],
            ['name' => 'users.create', 'group' => PermissionGroup::USERS, 'display_name' => ['en' => 'Create Users', 'fr' => 'Créer Utilisateurs', 'ar' => 'إنشاء مستخدمين']],
            ['name' => 'users.update', 'group' => PermissionGroup::USERS, 'display_name' => ['en' => 'Update Users', 'fr' => 'Modifier Utilisateurs', 'ar' => 'تعديل المستخدمين']],
            ['name' => 'users.suspend', 'group' => PermissionGroup::USERS, 'display_name' => ['en' => 'Suspend Users', 'fr' => 'Suspendre Utilisateurs', 'ar' => 'تجميد المستخدمين']],
            ['name' => 'users.roles.manage', 'group' => PermissionGroup::USERS, 'display_name' => ['en' => 'Manage Roles & Permissions', 'fr' => 'Gérer Rôles et Permissions', 'ar' => 'إدارة الأدوار والصلاحيات']],

            // Profiles
            ['name' => 'profiles.view', 'group' => PermissionGroup::PROFILES, 'display_name' => ['en' => 'View Profiles', 'fr' => 'Voir Profils', 'ar' => 'عرض الملفات']],
            ['name' => 'profiles.suspend', 'group' => PermissionGroup::PROFILES, 'display_name' => ['en' => 'Suspend Profiles', 'fr' => 'Suspendre Profils', 'ar' => 'تجميد الملفات']],
            ['name' => 'profiles.manage_templates', 'group' => PermissionGroup::PROFILES, 'display_name' => ['en' => 'Manage Templates', 'fr' => 'Gérer Modèles', 'ar' => 'إدارة القوالب']],

            // NFC Cards & Batches
            ['name' => 'cards.view', 'group' => PermissionGroup::CARDS, 'display_name' => ['en' => 'View Cards', 'fr' => 'Voir Cartes', 'ar' => 'عرض البطاقات']],
            ['name' => 'cards.create', 'group' => PermissionGroup::CARDS, 'display_name' => ['en' => 'Create Cards', 'fr' => 'Créer Cartes', 'ar' => 'إنشاء بطاقات']],
            ['name' => 'cards.update', 'group' => PermissionGroup::CARDS, 'display_name' => ['en' => 'Update Cards', 'fr' => 'Modifier Cartes', 'ar' => 'تعديل البطاقات']],
            ['name' => 'cards.suspend', 'group' => PermissionGroup::CARDS, 'display_name' => ['en' => 'Suspend Cards', 'fr' => 'Suspendre Cartes', 'ar' => 'تجميد البطاقات']],
            ['name' => 'cards.disable', 'group' => PermissionGroup::CARDS, 'display_name' => ['en' => 'Disable Cards', 'fr' => 'Désactiver Cartes', 'ar' => 'تعطيل البطاقات']],
            ['name' => 'cards.replace', 'group' => PermissionGroup::CARDS, 'display_name' => ['en' => 'Replace Cards', 'fr' => 'Remplacer Cartes', 'ar' => 'استبدال البطاقات']],
            ['name' => 'cards.transfer', 'group' => PermissionGroup::CARDS, 'display_name' => ['en' => 'Transfer Cards', 'fr' => 'Transférer Cartes', 'ar' => 'تحويل ملكية البطاقات']],
            ['name' => 'cards.reset_claim', 'group' => PermissionGroup::CARDS, 'display_name' => ['en' => 'Reset Claim Code', 'fr' => 'Réinitialiser Code d\'activation', 'ar' => 'إعادة تعيين رمز التفعيل']],
            ['name' => 'cards.rotate_token', 'group' => PermissionGroup::CARDS, 'display_name' => ['en' => 'Rotate Public Token', 'fr' => 'Régénérer Jeton Public', 'ar' => 'تجديد الرمز العام']],
            ['name' => 'card_batches.view', 'group' => PermissionGroup::CARDS, 'display_name' => ['en' => 'View Card Batches', 'fr' => 'Voir Lots de Cartes', 'ar' => 'عرض دفعات البطاقات']],
            ['name' => 'card_batches.create', 'group' => PermissionGroup::CARDS, 'display_name' => ['en' => 'Create Card Batches', 'fr' => 'Créer Lots de Cartes', 'ar' => 'إنشاء دفعات البطاقات']],

            // Subscriptions & Payments
            ['name' => 'subscriptions.view', 'group' => PermissionGroup::SUBSCRIPTIONS, 'display_name' => ['en' => 'View Subscriptions', 'fr' => 'Voir Abonnements', 'ar' => 'عرض الاشتراكات']],
            ['name' => 'subscriptions.manage', 'group' => PermissionGroup::SUBSCRIPTIONS, 'display_name' => ['en' => 'Manage Plans & Subscriptions', 'fr' => 'Gérer Plans et Abonnements', 'ar' => 'إدارة الباقات والاشتراكات']],
            ['name' => 'payments.review', 'group' => PermissionGroup::PAYMENTS, 'display_name' => ['en' => 'Review & Approve Payments', 'fr' => 'Vérifier et Valider Paiements', 'ar' => 'مراجعة واعتماد المدفوعات']],

            // Settings & Audit
            ['name' => 'settings.view', 'group' => PermissionGroup::SETTINGS, 'display_name' => ['en' => 'View Settings', 'fr' => 'Voir Paramètres', 'ar' => 'عرض الإعدادات']],
            ['name' => 'settings.update', 'group' => PermissionGroup::SETTINGS, 'display_name' => ['en' => 'Update Settings', 'fr' => 'Modifier Paramètres', 'ar' => 'تعديل الإعدادات']],
            ['name' => 'audit_logs.view', 'group' => PermissionGroup::AUDIT_LOGS, 'display_name' => ['en' => 'View Audit Logs', 'fr' => 'Consulter Journal d\'Audit', 'ar' => 'عرض سجل العمليات']],
        ];

        $createdPermissions = [];
        foreach ($permissions as $perm) {
            $createdPermissions[$perm['name']] = Permission::firstOrCreate(
                ['name' => $perm['name']],
                [
                    'group' => $perm['group'],
                    'display_name' => $perm['display_name'],
                    'guard_name' => 'web',
                ]
            );
        }

        // 2. Create Roles
        $superAdminRole = Role::firstOrCreate(
            ['name' => RoleName::SUPER_ADMIN->value],
            [
                'display_name' => ['en' => 'Super Administrator', 'fr' => 'Super Administrateur', 'ar' => 'المشرف العام'],
                'description' => 'Full administrative access and system control.',
            ]
        );

        $adminRole = Role::firstOrCreate(
            ['name' => RoleName::ADMIN->value],
            [
                'display_name' => ['en' => 'Administrator', 'fr' => 'Administrateur', 'ar' => 'مدير النظام'],
                'description' => 'Administrative management of users, cards, and payments.',
            ]
        );

        $supportRole = Role::firstOrCreate(
            ['name' => RoleName::SUPPORT->value],
            [
                'display_name' => ['en' => 'Customer Support', 'fr' => 'Support Client', 'ar' => 'الدعم الفني'],
                'description' => 'Read-only access and basic user support.',
            ]
        );

        $customerRole = Role::firstOrCreate(
            ['name' => RoleName::CUSTOMER->value],
            [
                'display_name' => ['en' => 'Customer', 'fr' => 'Client', 'ar' => 'زبون'],
                'description' => 'Standard customer with access to profile builder and NFC cards.',
            ]
        );

        // 3. Assign Permissions to Roles
        // Super Admin gets all permissions
        $superAdminRole->permissions()->sync(collect($createdPermissions)->pluck('id'));

        // Admin gets operational permissions
        $adminRole->permissions()->sync(
            collect($createdPermissions)->filter(function ($p, $name) {
                return ! in_array($name, ['users.roles.manage', 'settings.update'], true);
            })->pluck('id')
        );

        // Support gets read/view permissions
        $supportRole->permissions()->sync(
            collect($createdPermissions)->filter(function ($p, $name) {
                return str_contains($name, '.view') || $name === 'cards.reset_claim';
            })->pluck('id')
        );

        // Customer gets zero admin permissions

        // 4. Create Development Baseline Users (Local Development Seeders ONLY - Strictly prohibited in production)
        if (app()->isProduction()) {
            return;
        }

        $usersData = [
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@auracard.ma',
                'password' => 'AuraAdmin2026!#',
                'role' => $superAdminRole,
                'locale' => 'fr',
            ],
            [
                'name' => 'Operations Admin',
                'email' => 'admin@auracard.ma',
                'password' => 'AuraAdmin2026!#',
                'role' => $adminRole,
                'locale' => 'fr',
            ],
            [
                'name' => 'Support Agent',
                'email' => 'support@auracard.ma',
                'password' => 'AuraSupport2026!#',
                'role' => $supportRole,
                'locale' => 'fr',
            ],
            [
                'name' => 'Sample Customer',
                'email' => 'customer@auracard.ma',
                'password' => 'AuraCustomer2026!#',
                'role' => $customerRole,
                'locale' => 'ar',
            ],
        ];

        foreach ($usersData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                    'account_status' => AccountStatus::ACTIVE,
                    'locale' => $data['locale'],
                    'email_verified_at' => now(),
                ]
            );

            $user->roles()->syncWithoutDetaching([$data['role']->id]);
        }
    }
}
