<?php

namespace App\Models\Reference;

use Illuminate\Database\Eloquent\Model;

class ReadOnlyModel extends Model
{
    public $timestamps = false;

    protected $guarded = ['*'];
    
    protected static function booted()
    {
        static::creating(fn () => throw new \RuntimeException('Read-only model'));
        static::updating(fn () => throw new \RuntimeException('Read-only model'));
        static::deleting(fn () => throw new \RuntimeException('Read-only model'));
    }

    public function save(array $options = [])
    {
        throw new \RuntimeException('Read-only model');
    }
}
