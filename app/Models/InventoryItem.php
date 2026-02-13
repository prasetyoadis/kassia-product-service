<?php

namespace App\Models;

use App\Models\Reference\Outlet;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InventoryItem extends Model
{
    use HasUlids;

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
            'outlet_id' => 'string',
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
            'product_variant_id'
        );
    }

    public function outlet() {
        return $this->belongsTo(Outlet::class);
    }

    public function logs()
    {
        return $this->hasMany(InventoryLog::class);
    }
}
