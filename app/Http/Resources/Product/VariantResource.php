<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\cashier\InventoryLogResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $inventory = $this->whenLoaded('inventoryItem');

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'variant_name' => $this->variant_name,
            'description' => $this->description,
            'harga_awal' => $this->harga_awal,
            'stock' => $inventory?->current_stock ?? 0,
            'min_stock' => $inventory?->min_stock ?? 0,
            'transaction_item' => $this->whenLoaded('transactionItem', fn () =>
                $this->transactionItem 
                    ? [
                        'id' => $this->transactionItem->id,
                        'harga_jual' => $this->transactionItem->harga_jual,
                    ]
                    : null
            ),
            'history_stock' => $this->when(
                $inventory && $inventory->relationLoaded('logs'),
                fn () => InventoryLogResource::collection($inventory->logs),
                []
            ),
            'is_active' => (bool) $this->is_active
        ];
    }
}
