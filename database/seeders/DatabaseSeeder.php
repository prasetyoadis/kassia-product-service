<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $this->call([
        //     CategorySeeder::class,
        // ]);
        $menuUtama = Category::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            'name' => 'Menu Utama',
            'slug' => 'menu-utama',
            'description' => 'Menu Utama'
        ]);
        $makanan = Category::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            'name' => 'Makanan',
            'slug' => 'makanan',
            'description' => 'Makanan'
        ]);
        $minuman = Category::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            'name' => 'Minuman',
            'slug' => 'minuman',
            'description' => 'Minuman'
        ]);
        $snack = Category::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            'name' => 'Snack',
            'slug' => 'snack',
            'description' => 'Makanan ringan'
        ]);
        $pedas = Category::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            'name' => 'Pedas',
            'slug' => 'pedas',
            'description' => 'Pedas'
        ]);
        
        $product1 = Product::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            "name" => "Sego Njamoer Original",
            "description" => "Nasi putih dengan jamur krispi khas Sego Njamoer.",
            "is_variant" => "true"
        ]);
        $product2 = Product::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            "name" => "Sego Njamoer Sambal Ijo",
            "description" => "Sego Njamoer dengan sambal ijo khas.",
            "is_variant" => "true"
        ]);
        $product3 = Product::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            "name" => "Sego Njamoer Teriyaki",
            "description" => "Jamur saus teriyaki manis gurih.",
            "is_variant" => "true"
        ]);
        $product4 = Product::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            "name" => "Jamur Crispy Original",
            "description" => "Jamur krispi gurih tanpa saus.",
            "is_variant" => "true"
        ]);
        $product5 = Product::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            "name" => "Jamur Crispy Balado",
            "description" => "Jamur krispi dengan bumbu balado pedas.",
            "is_variant" => "true"
        ]);
        $product6 = Product::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            "name" => "Es Teh",
            "description" => "Es teh segar.",
            "is_variant" => "false"
        ]);

        $variant1Product1 = ProductVariant::create([
            "product_id" => $product1->id,
            "sku" => "SNJ-SBY-001",
            "variant_name" => "Reguler",
            "description" => "Porsi Standar",
            "harga_awal" => 15000,
        ]);

        $variant2Product1 = ProductVariant::create([
            "product_id" => $product1->id,
            "sku" => "SNJ-SBY-002",
            "variant_name" => "Jumbo",
            "description" => "Porsi Lebih Besar",
            "harga_awal" => 18000,
        ]);

        $variant1Product2 = ProductVariant::create([
            "product_id" => $product2->id,
            "sku" => "SNJ-SBY-003",
            "variant_name" => "Reguler",
            "description" => "Pedas sedang",
            "harga_awal" => 16000,
        ]);

        $variant2Product2 = ProductVariant::create([
            "product_id" => $product2->id,
            "sku" => "SNJ-SBY-004",
            "variant_name" => "Pedas",
            "description" => "Pedas standart",
            "harga_awal" => 18000,
        ]);

        $variant1Product3 = ProductVariant::create([
            "product_id" => $product3->id,
            "sku" => "SNJ-SBY-005",
            "variant_name" => "Reguler",
            "description" => "Rasa manis gurih",
            "harga_awal" => 17000,
        ]);

        $variant1Product4 = ProductVariant::create([
            "product_id" => $product4->id,
            "sku" => "SNJ-SBY-006",
            "variant_name" => "Small",
            "description" => "Porsi kecil",
            "harga_awal" => 10000,
        ]);

        $variant2Product4 = ProductVariant::create([
            "product_id" => $product4->id,
            "sku" => "SNJ-SBY-007",
            "variant_name" => "Large",
            "description" => "Porsi besar",
            "harga_awal" => 14000,
        ]);

        $variant1Product5 = ProductVariant::create([
            "product_id" => $product5->id,
            "sku" => "SNJ-SBY-008",
            "variant_name" => "Pedas",
            "description" => "Pedas standart",
            "harga_awal" => 12000,
        ]);

        $variant2Product5 = ProductVariant::create([
            "product_id" => $product5->id,
            "sku" => "SNJ-SBY-009",
            "variant_name" => "Extra Pedas",
            "description" => "Cabai ekstra",
            "harga_awal" => 15000,
        ]);

        $variantProduct6 = ProductVariant::create([
            "product_id" => $product6->id,
            "sku" => "SNJ-SBY-0010",
            "variant_name" => "Es Teh",
            "description" => "Es teh segar.",
            "harga_awal" => 15000,
        ]);

        $invenItem1 = InventoryItem::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            'product_variant_id' => $variant1Product1->id,
            'current_stock' => 8,
            'min_stock' => 5
        ]);
        InventoryLog::create([
            'created_by' => "a1096730-b723-4542-91af-983dcd04c409",
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            "inventory_item_id" => $invenItem1->id,
            'quantity' => 8,
            'total' => 8,
            'type' => 'in',
            'note' => "Stock pertama masuk"
        ]);
        $invenItem2 = InventoryItem::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            'product_variant_id' => $variant2Product1->id,
            'current_stock' => 8,
            'min_stock' => 5
        ]);
        InventoryLog::create([
            'created_by' => "a1096730-b723-4542-91af-983dcd04c409",
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            "inventory_item_id" => $invenItem2->id,
            'quantity' => 8,
            'total' => 8,
            'type' => 'in',
            'note' => "Stock pertama masuk"
        ]);
        $invenItem3 = InventoryItem::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            'product_variant_id' => $variant1Product2->id,
            'current_stock' => 6,
            'min_stock' => 5
        ]);
        InventoryLog::create([
            'created_by' => "a1096730-b723-4542-91af-983dcd04c409",
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            "inventory_item_id" => $invenItem3->id,
            'quantity' => 6,
            'total' => 6,
            'type' => 'in',
            'note' => "Stock pertama masuk"
        ]);

        $invenItem4 = InventoryItem::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            'product_variant_id' => $variant2Product2->id,
            'current_stock' => 5,
            'min_stock' => 5
        ]);
        InventoryLog::create([
            'created_by' => "a1096730-b723-4542-91af-983dcd04c409",
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            "inventory_item_id" => $invenItem4->id,
            'quantity' => 5,
            'total' => 5,
            'type' => 'in',
            'note' => "Stock pertama masuk"
        ]);

        $invenItem6 = InventoryItem::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            'product_variant_id' => $variant1Product3->id,
            'current_stock' => 4,
            'min_stock' => 5
        ]);

        InventoryLog::create([
            'created_by' => "a1096730-b723-4542-91af-983dcd04c409",
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            "inventory_item_id" => $invenItem6->id,
            'quantity' => 4,
            'total' => 4,
            'type' => 'in',
            'note' => "Stock pertama masuk"
        ]);

        $invenItem7 = InventoryItem::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            'product_variant_id' => $variant1Product4->id,
            'current_stock' => 4,
            'min_stock' => 5
        ]);

        InventoryLog::create([
            'created_by' => "a1096730-b723-4542-91af-983dcd04c409",
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            "inventory_item_id" => $invenItem7->id,
            'quantity' => 4,
            'total' => 4,
            'type' => 'in',
            'note' => "Stock pertama masuk"
        ]);

        $invenItem8 = InventoryItem::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            'product_variant_id' => $variant2Product4->id,
            'current_stock' => 9,
            'min_stock' => 5
        ]);

        InventoryLog::create([
            'created_by' => "a1096730-b723-4542-91af-983dcd04c409",
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            "inventory_item_id" => $invenItem8->id,
            'quantity' => 9,
            'total' => 9,
            'type' => 'in',
            'note' => "Stock pertama masuk"
        ]);

        $invenItem9 = InventoryItem::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            'product_variant_id' => $variant1Product5->id,
            'current_stock' => 13,
            'min_stock' => 5
        ]);

        InventoryLog::create([
            'created_by' => "a1096730-b723-4542-91af-983dcd04c409",
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            "inventory_item_id" => $invenItem9->id,
            'quantity' => 13,
            'total' => 13,
            'type' => 'in',
            'note' => "Stock pertama masuk"
        ]);

        $invenItem10 = InventoryItem::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            'product_variant_id' => $variant2Product5->id,
            'current_stock' => 8,
            'min_stock' => 5
        ]);

        InventoryLog::create([
            'created_by' => "a1096730-b723-4542-91af-983dcd04c409",
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            "inventory_item_id" => $invenItem10->id,
            'quantity' => 8,
            'total' => 8,
            'type' => 'in',
            'note' => "Stock pertama masuk"
        ]);

        $invenItem11 = InventoryItem::create([
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            'product_variant_id' => $variantProduct6->id,
            'current_stock' => 0,
            'min_stock' => 5
        ]);

        InventoryLog::create([
            'created_by' => "a1096730-b723-4542-91af-983dcd04c409",
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            "inventory_item_id" => $invenItem11->id,
            'quantity' => 6,
            'total' => 6,
            'type' => 'in',
            'note' => "Stock pertama masuk"
        ]);

        InventoryLog::create([
            'created_by' => "a1096730-b723-4542-91af-983dcd04c409",
            'outlet_id' => env('TEST_ACTIVE_OUTLET'),
            "inventory_item_id" => $invenItem11->id,
            'quantity' => 6,
            'total' => 0,
            'type' => 'out',
            'note' => "teh dibuang karena basi"
        ]);

        $product1->categories()->sync([$makanan->id, $menuUtama->id]);
        $product2->categories()->sync([$makanan->id, $menuUtama->id, $pedas->id]);
        $product3->categories()->sync([$makanan->id, $menuUtama->id]);
        $product4->categories()->sync([$snack->id, $menuUtama->id]);
        $product5->categories()->sync([$snack->id, $pedas->id]);
        $product6->categories()->sync([$minuman->id, $menuUtama->id]);
    }
}
