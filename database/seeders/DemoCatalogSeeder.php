<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect(['Phones', 'Laptops', 'Accessories', 'Audio'])
            ->mapWithKeys(fn (string $name) => [$name => Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            )]);

        $brands = collect(['Apple', 'Samsung', 'HP', 'Generic'])
            ->mapWithKeys(fn (string $name) => [$name => Brand::firstOrCreate(['name' => $name])]);

        $pcs = Unit::firstOrCreate(['name' => 'Piece'], ['abbreviation' => 'pcs']);

        $products = [
            ['category' => 'Phones', 'name' => 'iPhone 13', 'sku' => 'PHN-IP13', 'brand' => 'Apple', 'cost_price' => 150000, 'selling_price' => 200000, 'track_serial' => true, 'reorder_level' => 3],
            ['category' => 'Phones', 'name' => 'Samsung Galaxy A54', 'sku' => 'PHN-SGA54', 'brand' => 'Samsung', 'cost_price' => 110000, 'selling_price' => 145000, 'track_serial' => true, 'reorder_level' => 3],
            ['category' => 'Laptops', 'name' => 'HP Pavilion 15', 'sku' => 'LAP-HP15', 'brand' => 'HP', 'cost_price' => 250000, 'selling_price' => 320000, 'track_serial' => true, 'reorder_level' => 2],
            ['category' => 'Accessories', 'name' => 'USB-C Charger 20W', 'sku' => 'ACC-USBC20', 'brand' => 'Generic', 'cost_price' => 3500, 'selling_price' => 6000, 'track_serial' => false, 'reorder_level' => 10],
            ['category' => 'Accessories', 'name' => 'Phone Case (Universal)', 'sku' => 'ACC-CASE01', 'brand' => 'Generic', 'cost_price' => 1000, 'selling_price' => 2500, 'track_serial' => false, 'reorder_level' => 15],
            ['category' => 'Audio', 'name' => 'Bluetooth Earbuds', 'sku' => 'AUD-BTE01', 'brand' => 'Generic', 'cost_price' => 8000, 'selling_price' => 15000, 'track_serial' => false, 'reorder_level' => 5],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['sku' => $product['sku']],
                [
                    'category_id' => $categories[$product['category']]->id,
                    'name' => $product['name'],
                    'brand_id' => $brands[$product['brand']]->id,
                    'cost_price' => $product['cost_price'],
                    'selling_price' => $product['selling_price'],
                    'unit_id' => $pcs->id,
                    'track_serial' => $product['track_serial'],
                    'reorder_level' => $product['reorder_level'],
                    'is_active' => true,
                ]
            );
        }

        $suppliers = [
            ['name' => 'Auchi Gadget Distributors', 'contact_person' => 'Ifeanyi Okoro', 'phone' => '08031234567', 'email' => 'sales@auchigadgets.test'],
            ['name' => 'Lagos Mobile Wholesale', 'contact_person' => 'Chika Nwosu', 'phone' => '08059876543', 'email' => 'orders@lagosmobile.test'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(['phone' => $supplier['phone']], $supplier);
        }

        $customers = [
            ['name' => 'Walk-in Customer', 'phone' => '00000000000'],
            ['name' => 'Blessing Adeyemi', 'phone' => '08145678901', 'email' => 'blessing.a@example.test'],
            ['name' => 'Musa Ibrahim', 'phone' => '08167890123', 'email' => 'musa.i@example.test'],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(['phone' => $customer['phone']], $customer);
        }
    }
}
