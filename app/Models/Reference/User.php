<?php

namespace App\Models\Reference;

use App\Models\InventoryLog;
use App\Models\Reference\Subscription;

class User extends ReadOnlyModel
{
    protected $table = 'users';

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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'deleted_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at'   => 'datetime',
        ];
    }

    public function subscriptions() {
        return $this->belongsToMany(
            Subscription::class, 
            'subscription_user', 
            'user_id', 
            'subscription_id'
        )->withTimestamps();
    }
    public function createdInventoryLogs() {
        return $this->hasMany(InventoryLog::class, 'created_by');
    }

    /**
     * Pemeriksaan subscription status active/expired.
     * 
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Pemeriksaan status subscription user ada/active/expired.
     * 
     * @return bool
     */
    public function hasValidSubscription(): bool 
    {
        return $this->subscriptions()->active()->exists();
    }
}
