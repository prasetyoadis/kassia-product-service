<?php

namespace App\Models\Reference;

use App\Models\Reference\User;

class Subscription extends ReadOnlyModel
{
    protected $table = 'subscriptions';
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
            'valid_from' => 'datetime',
            'valid_to'   => 'datetime',
        ];
    }

    /**
     * Relation Model
     * 
     * 
     */
    public function users() {
        return $this->belongsToMany(
            User::class, 
            'subscription_user', 
            'subscription_id', 
            'user_id'
        )->withTimestamps();
    }
    /**
     * Pemeriksaan subscription status active/expired.
     * 
     * @param mixed $query
     * @return query
     */
    public function scopeActive($query)
    {
        $now = now();

        return $query->where('subscription_status', 'active')
                    ->where('valid_from', '<=', $now)
                    ->where('valid_to', '>=', $now);
    }
}
