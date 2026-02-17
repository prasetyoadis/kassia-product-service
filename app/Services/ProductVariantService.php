<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ProductVariantService {
    public function store(Product $product, string $outletId, array $payload)
    {
        DB::beginTransaction();

        try {
    
            if (array_key_exists('variants', $payload)) {
                /*
                * Bulk Insert ProductVariant
                */
                $variantRows = $this->buildVariantRows(
                    productId: $product->id,
                    payload: $payload
                );

                $this->bulkInsertVariants($variantRows);

                /*
                * Bulk Insert InventoryItem
                */
                $inventoryRows = $this->buildInventoryRows(
                    outletId: $outletId,
                    variantRows: $variantRows
                );

                $this->bulkInsertInventory($inventoryRows);

                DB::commit();

                return $variantRows;
            } else {
                /*
                 * Jika product sebelumnya NON-variant
                 * - Ubah product.is_variant = true
                 * - Nonaktifkan variant pertama (default)
                 */
                if($product->is_variant === false){
                    # Update product jadi variant-based
                    $product->update(['is_variant' => true]);
                    # Nonaktifkan semua variant existing (harusnya cuma 1)
                    ProductVariant::where('product_id', $product->id)
                        ->where('is_active', true)
                        ->where('variant_name', $product->name)
                        ->update([
                            'is_active' => false,
                            'updated_at' => now(),
                        ]);
                    $product->refresh();
                };
                
                /*
                 * Create Variant Baru
                 */
                $variantPayload = collect($payload)->except(['min_stock'])->all();
                $variantPayload['product_id'] = $product->id;
                $variantPayload['outlet_id'] = $outletId;

                $variant = ProductVariant::create($variantPayload);

                /*
                 * Create Inventory
                 */
                InventoryItem::create([
                    'outlet_id' => $outletId,
                    'product_variant_id' => $variant->id,
                    'current_stock' => 0,
                    'min_stock' => $payload['min_stock'] ?? 0,
                ]);

                DB::commit();

                return $variant;
            }

            Log::info('kassia-product-service.ProductVariantController@store ProductVariantService@store success', [
                'service' => 'ProductVariantService',
                'action' => 'store',
                'product_id' => $product->id,
                'payload' => $payload,
            ]);

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('kassia-product-service.ProductVariantController@store ProductService@store failed', [
                'service' => 'ProductService',
                'action' => 'store',
                'product_id' => $product->id,
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function update(Product $product, ProductVariant $variant, array $payload): ProductVariant
    {
        DB::beginTransaction();

        try {
            /*
             * GUARD: product tanpa variant
             */
            // if ($product->is_variant === false) {
            //     throw new \DomainException('Product has no multiple variants');
            // }

            /*
             * OPTIONAL GUARD
             */
            // if ($product->is_active === false) {
            //     throw new \DomainException('Product is inactive');
            // }

            /*
             * UPDATE Variant (exclude min_stock)
             */
            $variantPayload = collect($payload)->except(['min_stock'])->all();

            if (!empty($variantPayload)) {
                $variant->fill($variantPayload);
                $variant->save();
            }

            /*
            * UPDATE InventoryItem.min_stock (only if payload exists)
            */
            if (array_key_exists('min_stock', $payload)) {
                InventoryItem::where('product_variant_id', $variant->id)
                    ->update([
                        'min_stock' => $payload['min_stock'],
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();

            Log::info('kassia-product-service.ProductVariantController@update.ProductVariantService.update success', [
                'service' => 'ProductVariantService',
                'action' => 'update',
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'payload' => $payload,
            ]);

            return $variant;

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('kassia-product-service.ProductVariantController@update.ProductVariantService.update failed', [
                'service' => 'ProductVariantService',
                'action' => 'update',
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function updateStock(string $variantId, string $userId, array $payload ): InventoryItem
    {
        DB::beginTransaction();

        try {
            $outletId = Cache::get("active_outlet:user:{$userId}");

            if (!$outletId) {
                throw new \RuntimeException('Active outlet not found');
            }

            $inventory = InventoryItem::where('product_variant_id', $variantId)
                ->where('outlet_id', $outletId)
                ->lockForUpdate()
                ->firstOrFail();

            $qty = $payload['quantity'];

            /*
             * Hitung stock baru
             */
            if ($payload['type'] === 'out') {
                if ($inventory->current_stock < $qty) {
                    throw new \DomainException('Stock insufficient');
                }

                $inventory->current_stock -= $qty;
            } else {
                $inventory->current_stock += $qty;
            }

            $inventory->save();

            /*
             * LOG INVENTORY
             */
            InventoryLog::create([
                'inventory_item_id' => $inventory->id,
                'outlet_id' => $outletId,
                'created_by' => $userId,
                'quantity' => $qty,
                'total' => $inventory->current_stock,
                'type' => $payload['type'],
                'note' => $payload['note'] ?? null,
            ]);

            DB::commit();

            Log::info('kassia-product-service.ProductVariantController@updateStock.ProductVariantService.updateStock success', [
                'service' => 'VariantInventoryService',
                'action' => 'updateStock',
                'variant_id' => $variantId,
                'type' => $payload['type'],
                'quantity' => $qty,
                'current_stock' => $inventory->current_stock,
            ]);

            return $inventory;

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('kassia-product-service.ProductVariantController@updateStock.ProductVariantService.updateStock failed', [
                'service' => 'VariantInventoryService',
                'action' => 'updateStock',
                'variant_id' => $variantId,
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function adjustStock(string $variantId, string $userId, array $payload ): InventoryItem
    {
        DB::beginTransaction();

        try {
            $outletId = Cache::get("active_outlet:user:{$userId}") ?? env('TEST_ACTIVE_OUTLET');

            $inventory = InventoryItem::where('product_variant_id', $variantId)
                ->where('outlet_id', $outletId)
                ->lockForUpdate()
                ->firstOrFail();

            $oldStock = $inventory->current_stock;
            $newStock = $payload['actual_stock'];
            $diff     = $newStock - $oldStock;

            if ($diff === 0) {
                return $inventory; // no-op
            }

            $inventory->update([
                'current_stock' => $newStock,
            ]);

            InventoryLog::create([
                'inventory_item_id' => $inventory->id,
                'outlet_id' => $outletId,
                'created_by' => $userId,
                'quantity' => abs($diff),
                'total' => $newStock,
                'type' => 'correction',
                'note' => $payload['note'] ?? 'Stock correction',
            ]);

            DB::commit();

            Log::info('kassia-product-service.ProductVariantController@adjustStock.ProductVariantService.adjustStock success', [
                'service'        => 'VariantInventoryService',
                'action'         => 'adjustStock',
                'variant_id'     => $variantId,
                'type'           => 'correction',
                'quantity'       => abs($diff),
                'current_stock'  => $inventory->current_stock,
            ]);

            return $inventory;

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('kassia-product-service.ProductVariantController@adjustStock.ProductVariantService.adjustStock failed', [
                'service' => 'VariantInventoryService',
                'action' => 'adjustStock',
                'variant_id' => $variantId,
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function destroy(Product $product, ProductVariant $variant): ProductVariant
    {
        DB::beginTransaction();

        try {
            /*-----
             * GUARD: product tanpa multiple variant
             * --------------------------------- */
            if ($product->is_variant === false) {
                throw new \DomainException('Product has no multiple variants');
            }

            if ($product->is_active === false) {
                throw new \DomainException('Product is inactive');
            }

            if ($variant->is_active === false) {
                throw new \DomainException('Variant already inactive');
            }

            /*
             * GUARD: minimal 1 variant aktif
             */
            $activeVariantCount = ProductVariant::where('product_id', $product->id)
                ->where('is_active', true)
                ->count();

            if ($activeVariantCount <= 1) {
                throw new \DomainException('Cannot delete last active variant');
            }

            /*
             * SOFT DELETE Variant
             */
            $variant->update(['is_active' => false,]);
            $variant->delete();

            DB::commit();

            Log::info('kassia-product-service.ProductVariantController@destroy.ProductVariantService.destroy success', [
                'service' => 'ProductVariantService',
                'action' => 'destroy',
                'product_id' => $product->id,
                'variant_id' => $variant->id,
            ]);

            return $variant;

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('kassia-product-service.ProductVariantController@destroy.ProductVariantService.destroy failed', [
                'service' => 'ProductVariantService',
                'action' => 'destroy',
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function buildVariantRows(String $productId, array $payload): array
    {
        $now = now();
        $rows = [];

        foreach ($payload['variants'] as $variant) {
            $rows[] = [
                'id' => Str::ulid(),
                'product_id' => $productId,
                'sku' => $variant['sku'],
                'variant_name' => $variant['variant_name'],
                'description' => $variant['description'] ?? null,
                'harga_awal' => $variant['harga_awal'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
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
                'min_stock' => $payload['min_stock'] ?? 0,
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
