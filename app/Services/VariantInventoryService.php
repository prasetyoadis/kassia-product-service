<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryLog;
// use App\Models\Product;
// use App\Models\ProductVariant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Str;
use Throwable;

class VariantInventoryService {

    public function updateStock(string $productId, string $variantId, string $userId, array $payload ): InventoryItem
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

            /** -------------------------
             * LOG INVENTORY
             * -------------------------- */
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

            Log::info('kassia-product-service.InventoryItemController@updateStock VariantInventoryService@updateStock success', [
                'service' => 'VariantInventoryService',
                'action' => 'updateStock',
                'product_id' => $productId,
                'variant_id' => $variantId,
                'type' => $payload['type'],
                'quantity' => $qty,
                'current_stock' => $inventory->current_stock,
            ]);

            return $inventory;

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('kassia-product-service.InventoryItemController@updateStock VariantInventoryService@updateStock failed', [
                'service' => 'VariantInventoryService',
                'action' => 'updateStock',
                'product_id' => $productId,
                'variant_id' => $variantId,
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function adjustStock(string $productId, string $variantId, string $userId, array $payload ): InventoryItem
    {
        DB::beginTransaction();

        try {
            $outletId = Cache::get("active_outlet:user:{$userId}");

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

            Log::info('kassia-product-service.InventoryItemController@adjustStock VariantInventoryService@adjustStock success', [
                'service' => 'VariantInventoryService',
                'action' => 'adjustStock',
                'product_id' => $productId,
                'variant_id' => $variantId,
                'type' => 'correction',
                'quantity' => abs($diff),
                'current_stock' => $inventory->current_stock,
            ]);

            return $inventory;

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('kassia-product-service.InventoryItemController@adjustStock VariantInventoryService@adjustStock failed', [
                'service' => 'VariantInventoryService',
                'action' => 'adjustStock',
                'product_id' => $productId,
                'variant_id' => $variantId,
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
    
}