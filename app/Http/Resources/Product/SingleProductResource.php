<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Product\ImageResource;
use App\Http\Resources\Product\VariantResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SingleProductResource extends JsonResource
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
            'is_variant' => (bool) $this->is_variant,
            'variants' => VariantResource::collection(
                $this->whenLoaded('variants')
            ),
            'categories' => $this->whenLoaded('categories', fn () => 
                $this->categories->pluck('name')->values()
            ),
            'images' => ImageResource::collection(
                $this->whenLoaded('images')
            ),
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString()
        ];
    }
}
