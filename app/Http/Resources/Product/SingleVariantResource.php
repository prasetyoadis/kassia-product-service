<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SingleVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'variant_name' => $this->variant_name,
            // 'stock' => $this->inventoryItem?->current_stock ?? 0,
            // 'min_stock' => $this->inventoryItem?->min_stock ?? 0,
        ];
    }
}
