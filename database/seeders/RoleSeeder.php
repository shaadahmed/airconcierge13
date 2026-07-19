<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Business role names used for route guards (not Entrust schema).
     *
     * @var list<string>
     */
    public const ROLES = [
        'superadmin',
        'admin',
        'Regional Manager',
        'Property Owner',
        'cleaner',
        'maintenance',
    ];

    public function run(): void
    {
        try {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
        } catch (\Throwable) {
            // Cache store (e.g. Redis) may be unavailable outside Sail; roles still seed.
        }

        foreach (self::ROLES as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
