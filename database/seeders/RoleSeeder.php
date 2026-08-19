<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view dashboard',
            'manage products',
            'manage categories',
            'manage stock',
            'access pos',
            'view sales',
            'void sales',
            'view customers',
            'manage customers',
            'manage suppliers',
            'manage purchase orders',
            'manage purchase returns',
            'manage warranties',
            'manage repairs',
            'view reports',
            'manage users',
            'manage settings',
            'manage discount rules',
            'manage shifts',
            'view accounting',
            'manage chart of accounts',
            'manage stores',
            'manage expenses',
            'manage tax settings',
            'manage roles',
            'view system health',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        Role::firstOrCreate(['name' => 'Admin'])->syncPermissions($permissions);

        Role::firstOrCreate(['name' => 'Manager'])->syncPermissions([
            'view dashboard',
            'manage products',
            'manage categories',
            'manage stock',
            'view customers',
            'manage customers',
            'manage suppliers',
            'manage purchase orders',
            'manage purchase returns',
            'manage warranties',
            'manage repairs',
            'view reports',
            'manage discount rules',
            'manage shifts',
            'view accounting',
            'manage stores',
            'manage expenses',
        ]);

        Role::firstOrCreate(['name' => 'Cashier'])->syncPermissions([
            'view dashboard',
            'access pos',
            'view sales',
            'view customers',
        ]);
    }
}
