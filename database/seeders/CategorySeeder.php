<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $makanan = Category::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            'name' => 'Makanan',
            'slug' => 'makanan',
            'description' => 'Makanan'
        ]);
        Category::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            'name' => 'Minuman',
            'slug' => 'minuman',
            'description' => 'Minuman'
        ]);
        Category::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            'name' => 'Snack',
            'slug' => 'snack',
            'description' => 'Makanan ringan'
        ]);
        Category::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            'name' => 'Pedas',
            'slug' => 'pedas',
            'description' => 'Pedas'
        ]);
    }
}
