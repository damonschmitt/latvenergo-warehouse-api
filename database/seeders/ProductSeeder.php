<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('products')->insert([
            [
                'name' => 'Solar Panel 400W',
                'description' => 'High-efficiency photovoltaic solar panel',
                'price' => 249.99,
                'quantity' => 50,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Wind Turbine Controller',
                'description' => 'Controller unit for small wind turbines',
                'price' => 899.00,
                'quantity' => 15,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Energy Storage Battery',
                'description' => 'Lithium battery for energy storage systems',
                'price' => 1299.50,
                'quantity' => 20,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Smart Energy Meter',
                'description' => 'Digital meter for electricity consumption tracking',
                'price' => 149.75,
                'quantity' => 100,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'EV Charging Cable',
                'description' => 'Type 2 charging cable for electric vehicles',
                'price' => 199.00,
                'quantity' => 40,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
