<?php

namespace App\Models;

use App\Models\Reference\Outlet;
use App\Models\Reference\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InventoryLog extends Model
{
    use HasUlids;

    public const TYPE_IN  = 'in';
    public const TYPE_OUT = 'out';
    
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
     * Fungsi ketika model Eloquent selesai dimuat.
     *
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Relation Model
     * 
     * 
     */
    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function outlet() {
        return $this->belongsTo(Outlet::class);
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }
}
