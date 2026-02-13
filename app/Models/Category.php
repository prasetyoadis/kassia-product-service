<?php

namespace App\Models;

use App\Models\Reference\Outlet;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasUlids, SoftDeletes;

    /**
     * Primary key bukan auto-increment
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Tipe primary key
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Guarded attributes
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];
    
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'outlet_id' => 'string',
            'deleted_at' => 'datetime'
        ];
    }

    /**
     * Relation Model
     * 
     * 
     */
    public function products() {
        return $this->belongsToMany(
            Category::class, 
            'category_product', 
            'category_id', 
            'product_id'
        )->withTimestamps();
    }

    public function outlet() {
        return $this->belongsTo(Outlet::class);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            // Search
            ->when(
                $filters['search'] ?? null,
                fn ($q, $search) =>
                    $q->where('categories.name', 'ILIKE', "%{$search}%")
            )

            // Sorting
            ->when(
                $filters['sort_by'] ?? null,
                function ($q, $sortBy) use ($filters) {
                    $allowed = ['name', 'created_at', 'updated_at'];

                    if (!in_array($sortBy, $allowed, true)) {
                        return;
                    }

                    $order = strtolower($filters['sort_order'] ?? 'asc');
                    $order = in_array($order, ['asc', 'desc'], true)
                        ? $order
                        : 'asc';

                    $q->orderBy("categories.$sortBy", $order);
                }
            )

            // Safety
            ->select('categories.*');
    }
}
