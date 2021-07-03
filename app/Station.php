<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    protected $guarded = [];

    public function scopeDefault($query)
    {
        return $query->where('default', 1);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'station_items')->withPivot('amount');
    }
}
