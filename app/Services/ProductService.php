<?php

namespace App\Services;

use App\Helpers\GeneralResponse;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ProductService
{
    public function store(string $userId, array $payload): Product
    {
        DB::beginTransaction();

        try {
            // $outletId = Cache::get("active_outlet:user:{$userId}") ?? env('TEST_ACTIVE_OUTLET');

            if (!$payload['outlet_id']) {
                throw new \Exception('Outlet not found');
            }

            /*
             * CREATE Product 
             */
            $product = Product::create([
                'outlet_id' => $payload['outlet_id'],
                'name' => $payload['name'],
                'description' => $payload['description'] ?? null,
                'is_variant' => $payload['is_variant'],
                'is_active' => $payload['is_active'] ?? true
            ]);

            /*
             * Attach Categories
             */
            if (!empty($payload['categories'])) {
                $product->categories()->sync($payload['categories']);
            }

            if ($payload['is_variant'] === "0") {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $payload['sku'] ?? null,
                    'variant_name' => $product->name,
                    'description' => $product->description,
                    'harga_awal' => $payload['harga_awal'] ?? null,
                    'is_active' => true,
                ]);

                InventoryItem::create([
                    'outlet_id' => $payload['outlet_id'],
                    'product_variant_id' => $variant->id,
                    'current_stock' => 0,
                    'min_stock' => 0,
                ]);
            }

            /*
             * Bulk Insert ProductVariant
             */
            // $variantRows = $this->buildVariantRows(
            //     product: $product,
            //     payload: $payload
            // );

            // $this->bulkInsertVariants($variantRows);

            /*
             * Bulk Insert InventoryItem
             */
            // $inventoryRows = $this->buildInventoryRows(
            //     outletId: $outletId,
            //     variantRows: $variantRows
            // );

            // $this->bulkInsertInventory($inventoryRows);

            DB::commit();

            Log::info('kassia-product-service.ProductController@store.ProductService@store success', [
                'service' => 'ProductService',
                'action' => 'store',
                'payload' => $payload,
                'product_id' => $product->id,
            ]);
            
            return $product;

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('kassia-product-service.ProductController@store.ProductService@store failed', [
                'service' => 'ProductService',
                'action' => 'store',
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);

            // lempar lagi, BIAR CONTROLLER YANG HANDLE RESPONSE
            throw $e;
        }
    }

    public function update(Product $product, array $payload): Product
    {
        DB::beginTransaction();

        try {
            $wasVariant = $product->is_variant;
            /*
             * UPDATE Product
             */
            $product->fill($payload);
            $product->save();

            /*
            * UPDATE CATEGORIES
            */
            if (array_key_exists('categories', $payload)) {
                $product->categories()->sync($payload['categories'] ?? []);
            }

            /*
             * CASE A: Jika is_variant = FALSE
             * sync ke DEFAULT VARIANT
             */
            if ($wasVariant === false) {

                $variantPayload = [];

                if (array_key_exists('name', $payload)) {
                    $variantPayload['variant_name'] = $payload['name'];
                }

                if (array_key_exists('description', $payload)) {
                    $variantPayload['description'] = $payload['description'];
                }

                if (array_key_exists('harga_awal', $payload)) {
                    $variantPayload['harga_awal'] = $payload['harga_awal'];
                }

                if (!empty($variantPayload)) {
                    ProductVariant::where('product_id', $product->id)
                        ->update($variantPayload);
                }
            }

            /*
             * RULE GLOBAL: product non-aktif
             */
            if (
                array_key_exists('is_active', $payload)
                && $payload['is_active'] === false
            ) {
                ProductVariant::where('product_id', $product->id)
                    ->update(['is_active' => false]);
            }

            DB::commit();

            Log::info('kassia-product-service.ProductController@update.ProductService.update success', [
                'service' => 'ProductService',
                'action' => 'update',
                'product_id' => $product->id,
                'payload' => $payload,
            ]);
            
            return $product;

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('kassia-product-service.ProductImageController@update.ProductService.update failed', [
                'service' => 'ProductService',
                'action' => 'update',
                'product_id' => $product->id,
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function destroy(Product $product): Product
    {
        DB::beginTransaction();

        try {
            /*
             * Nonaktifkan Product
             */
            $product->update(['is_active' => false,]);

            /*
             * Nonaktifkan Variants
             */
            ProductVariant::where('product_id', $product->id)
                ->update(['is_active' => false]);

            /*
             * SOFT DELETE Product
             */
            $product->delete();

            DB::commit();

            Log::info('kassia-product-service.ProductImageController@destroy.ProductService.destroy success', [
                'service' => 'ProductService',
                'action' => 'destroy',
                'product_id' => $product->id,
            ]);

            return $product;

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('kassia-product-service.ProductImageController@destroy.ProductService.destroy failed', [
                'service' => 'ProductService',
                'action' => 'destroy',
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function bulkDestroy(array $productIds): void
    {
        DB::beginTransaction();

        try {
            $products = Product::whereIn('id', $productIds)
                ->get();

            if ($products->isEmpty()) {
                return;
            }

            $ids = $products->pluck('id')->toArray();

            /*
             * Nonaktifkan Products
             */
            Product::whereIn('id', $ids)->update([
                'is_active' => false,
            ]);

            /** -------------------------
             * Nonaktifkan Variants
             * -------------------------- */
            ProductVariant::whereIn('product_id', $ids)
                ->update(['is_active' => false]);

            /*
             * SOFT DELETE Prodcuts
             */
            Product::whereIn('id', $ids)->delete();

            DB::commit();

            Log::info('kassia-product-service.ProductImageController@bulkDestroy.ProductService.bulkDestroy success', [
                'service' => 'ProductService',
                'action' => 'bulkDestroy',
                'product_ids' => $ids,
            ]);

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('kassia-product-service.ProductImageController@bulkDestroy.ProductService.bulkDestroy failed', [
                'service' => 'ProductService',
                'action' => 'bulkDestroy',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function buildVariantRows(Product $product, array $payload): array
    {
        $now = now();
        $rows = [];

        # CASE 1: JIKA is_variant = FALSE
        if ($payload['is_variant'] === "0") {
            $rows[] = [
                'id' => Str::ulid(),
                'product_id' => $product->id,
                'sku' => $payload['sku'] ?? null,
                'variant_name' => $product->name,
                'description' => $product->description,
                'harga_awal' => $payload['harga_awal'] ?? null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        # CASE 2: JIKA is_variant = TRUE
        if ($payload['is_variant'] === "1") {
            foreach ($payload['variants'] as $variant) {
                $rows[] = [
                    'id' => Str::ulid(),
                    'product_id' => $product->id,
                    'sku' => $variant['sku'],
                    'variant_name' => $variant['variant_name'],
                    'description' => $variant['description'] ?? null,
                    'harga_awal' => $variant['harga_awal'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        return $rows;
    }

    private function bulkInsertVariants(array $variantRows): void
    {
        if (empty($variantRows)) {
            return;
        }

        ProductVariant::insert($variantRows);
    }

    private function buildInventoryRows(string $outletId, array $variantRows): array
    {
        $now = now();
        $rows = [];

        foreach ($variantRows as $variant) {
            $rows[] = [
                'id' => Str::ulid(),
                'outlet_id' => $outletId,
                'product_variant_id' => $variant['id'],
                'current_stock' => 0,
                'min_stock' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $rows;
    }

    private function bulkInsertInventory(array $inventoryRows): void
    {
        if (empty($inventoryRows)) {
            return;
        }

        InventoryItem::insert($inventoryRows);
    }

}
