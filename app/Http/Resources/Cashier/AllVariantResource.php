<?php

namespace App\Http\Resources\Cashier;

use App\Http\Resources\cashier\InventoryLogResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product = $this->whenLoaded('product');
        $inventory = $this->whenLoaded('inventoryItem');
        return [
            'id' => $this->id,
            'product_id' => $product->id,
            'sku' => $this->sku,
            'name' => $product->is_variant 
                    ? "{$product->name} {$this->variant_name}"
                    : $product->name,
            // 'description' => $this->description,
            // 'harga_awal' => $this->harga_awal,
            'stock' => $inventory?->current_stock ?? 0,
            'min_stock' => $inventory?->min_stock ?? 0,
            'is_active' => (bool) $this->is_active,
            'image' => $this->when(
                $product && $product->relationLoaded('defaultImage'),
                fn () => $product->defaultImage
                    ? [
                        'url' => $product->defaultImage->url,
                        'alt' => $product->defaultImage->alt,
                    ]
                    : null
            ),
        ];
    }
}
