<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasUlids, SoftDeletes;

    /**
     * The attributes that are mass public.
     *
     * @var bool
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
            'is_active' => 'boolean',
            'is_variant' => 'boolean',
            'deleted_at' => 'datetime'
        ];
    }

    /**
     * Relation Model
     * 
     * 
     */
    public function variants() {
        return $this->hasMany(ProductVariant::class);
    }
    public function images() {
        return $this->hasMany(ProductImage::class);
    }
    public function defaultImage(){
        return $this->hasOne(ProductImage::class)->where('is_default', true);
    }
    public function categories() {
        return $this->belongsToMany(
            Category::class, 
            'category_product', 
            'product_id', 
            'category_id'
        )->withTimestamps();
    }
    // Optional (kalau sering butuh stok by product)
    public function inventoryItems() {
        return $this->hasManyThrough(
            InventoryItem::class,
            ProductVariant::class,
            'product_id',         // FK di product_variants
            'product_variant_id', // FK di inventory_items
            'id',
            'id'
        );
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('products.name', 'ILIKE', "%{$search}%")
                    ->orWhereHas('variants', function ($qv) use ($search) {
                        $qv->where('variant_name', 'ILIKE', "%{$search}%")
                            ->orWhere('sku', 'ILIKE', "%{$search}%");
                    });
                });
            })

            ->when($filters['category'] ?? null, function ($q, $categoryUlid) {
                $q->whereHas('categories', fn ($qq) =>
                    $qq->where('categories.id', $categoryUlid)
                );
            })

            ->when($filters['sort_by'] ?? null, function ($q, $sortBy) use ($filters) {
                $allowed = ['name', 'created_at', 'updated_at'];
                if (!in_array($sortBy, $allowed)) {
                    return;
                }

                $order = strtolower($filters['order'] ?? 'asc');
                $order = in_array($order, ['asc', 'desc']) ? $order : 'asc';

                $q->orderBy("products.$sortBy", $order);
            });
    }


    public function scopeWithStockSummary(Builder $q): Builder
    {
        return $q
            ->leftJoin('product_variants as pv', 'pv.product_id', '=', 'products.id')
            ->leftJoin('inventory_items as ii', function ($join) {
                $join->on('ii.product_variant_id', '=', 'pv.id')
                    ->on('ii.outlet_id', '=', 'products.outlet_id');
            })
            ->select(
                'products.*',
                DB::raw('COALESCE(SUM(ii.current_stock), 0) as total_stock'),
                DB::raw('MIN(ii.min_stock) as min_stock')
            )
            ->groupBy('products.id');
    }

}
