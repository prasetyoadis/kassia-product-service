<?php

namespace App\Models\Reference;

use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\InventoryLog;
use Illuminate\Database\Eloquent\Model;

class Outlet extends ReadOnlyModel
{
    protected $table = 'outlets';

    // biasanya outlet id uuid
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
    */
    public function categories() {
        return $this->hasMany(Category::class);
    }
    public function inventories() {
        return $this->hasMany(InventoryItem::class);
    }
    public function Invenlogs() {
        return $this->hasMany(InventoryLog::class);
    }
}
