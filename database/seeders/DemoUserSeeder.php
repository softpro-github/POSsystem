<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $mainStoreId = Store::where('name', 'Main Store')->value('id');

        $admin = User::firstOrCreate(
            ['email' => 'admin@gadgetstore.test'],
            ['name' => 'Store Admin', 'password' => 'password', 'is_active' => true, 'store_id' => $mainStoreId]
        );
        $admin->syncRoles(['Admin']);

        $manager = User::firstOrCreate(
            ['email' => 'manager@gadgetstore.test'],
            ['name' => 'Store Manager', 'password' => 'password', 'is_active' => true, 'store_id' => $mainStoreId]
        );
        $manager->syncRoles(['Manager']);

        $cashier = User::firstOrCreate(
            ['email' => 'cashier@gadgetstore.test'],
            ['name' => 'Store Cashier', 'password' => 'password', 'is_active' => true, 'store_id' => $mainStoreId]
        );
        $cashier->syncRoles(['Cashier']);
    }
}
