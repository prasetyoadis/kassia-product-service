<?php

namespace App\Models\Reference;

use App\Models\ProductVariant;
use App\Models\Reference\ReadOnlyModel;

class TransactionItem extends ReadOnlyModel
{
    protected $table = 'transaction_items';

    /**
     * The attributes that are mass protecable.
     *
     * @var list<string>, String
     */
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at'   => 'datetime',
        ];
    }

    /**
     * Relation Model
     * 
     * 
     */
    public function variant() {
        return $this->belongsTo(
            ProductVariant::class,
            'product_variant_id',
            'id'
        );
    }
}
