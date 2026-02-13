<?php

namespace App\Http\Resources\Cashier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllProductSimpleResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        $variant = $this->whenLoaded('variants')?->first();
        $inventory = $variant?->relationLoaded('inventoryItem')
            ? $variant->inventoryItem
            : null;
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'harga_awal' => $variant?->harga_awal ?? 0,
            'stock' => $inventory?->current_stock ?? 0,
            'is_variant' => (bool) $this->is_variant,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
