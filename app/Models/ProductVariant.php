<?php

namespace App\Models;

use App\Models\Reference\TransactionItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    use HasUlids, SoftDeletes;

    /**
     * The attributes that are mass public.
     *
     * @var list<string>
     */
    public $incrementing = false;

    /**
     * The attributes that are mass protecable.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];
    protected $keyType = 'string';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'harga_awal' => 'integer',
            'is_active' => 'boolean',
            'deleted_at' => 'datetime'
        ];
    }

    /**
     * Relation Model
     * 
     * 
     */
    public function product() {
        return $this->belongsTo(Product::class);
    }
    public function inventoryItem() {
        return $this->hasOne(InventoryItem::class);
    }
    public function transactionItem() {
        return $this->hasOne(
            TransactionItem::class,
            'product_variant_id',
            'id'
        );
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            # Search
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('product_variants.variant_name', 'ILIKE', "%{$search}%")
                    ->orWhere('product_variants.sku', 'ILIKE', "%{$search}%")
                    ->orWhereHas('product', function ($qv) use ($search) {
                        $qv->where('name', 'ILIKE', "%{$search}%");
                    });
                });
            })

            # Category
            ->when($filters['category'] ?? null, function ($q, $categoryUlid) {
                $q->whereHas('product.categories', fn ($qq) =>
                    $qq->where('categories.id', $categoryUlid)
                );
            })

            /*
             * STOCK FILTER (LEVEL: PRODUCT_VARIANT)
             *
             * out_of_stock :
             *   - inventory_item tidak ada
             *   - current_stock <= 0
             *
             * low_stock :
             *   - current_stock > 0
             *   - current_stock <= min_stock (SET USER)
             */
            
            ->when(
                !empty($filters['low_stock']) || !empty($filters['out_of_stock']),
                function ($q) use ($filters) {

                    if (!empty($filters['out_of_stock'])) {
                        $q->where(function ($qq) {
                            $qq->whereDoesntHave('inventoryItem')
                            ->orWhereHas('inventoryItem', fn ($qi) =>
                                $qi->where('current_stock', '<=', 0)
                            );
                        });
                    }

                    if (!empty($filters['low_stock'])) {
                        $q->whereHas('inventoryItem', fn ($qi) =>
                            $qi->where('current_stock', '>', 0)
                            ->whereColumn(
                                'inventory_items.current_stock',
                                '<=',
                                'inventory_items.min_stock'
                            )
                        );
                    }
                }
            )

            # WAJIB
            ->select('product_variants.*');
    }
}
