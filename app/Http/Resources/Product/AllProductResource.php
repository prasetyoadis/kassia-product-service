<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllProductResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'total_stock' => (int) ($this->total_stock ?? 0),
            'min_stock' => $this->min_stock !== null
                ? (int) $this->min_stock
                : null,
                // 'variants' => SingleVariantResource::collection(
                //     $this->whenLoaded('variants')
                // ),
                'categories' => $this->whenLoaded('categories', fn () => 
                $this->categories->pluck('name')->values()
                ),
                'image' => $this->whenLoaded('defaultImage', fn () =>
                $this->defaultImage 
                ? [
                    'url' => $this->defaultImage->url,
                    'alt' => $this->defaultImage->alt,
                    'is_default' => true,
                ]
                : null
                ),
            'is_variant' => (bool) $this->is_variant,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString()
        ];
    }
}
