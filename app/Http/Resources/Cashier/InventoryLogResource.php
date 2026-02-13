<?php

namespace App\Http\Resources\cashier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->type,
            'quantity' => $this->quantity,
            'total' => $this->total,
            'note' => $this->note,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
