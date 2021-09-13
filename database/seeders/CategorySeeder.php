<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Category::insert([
            [
                'name' => 'General',
                'icon' => 'mdi-tag',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Groceries',
                'icon' => 'mdi-cart',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Food',
                'icon' => 'mdi-food-fork-drink',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Party',
                'icon' => 'mdi-party-popper',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Car',
                'icon' => 'mdi-car',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Trip',
                'icon' => 'mdi-road-variant',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pets',
                'icon' => 'mdi-paw',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Clothes',
                'icon' => 'mdi-tshirt-crew',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Health',
                'icon' => 'mdi-hospital-box',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Games',
                'icon' => 'mdi-google-controller',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Devices',
                'icon' => 'mdi-devices',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
