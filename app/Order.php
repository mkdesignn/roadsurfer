<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    public function scopeSelectOneMonth($query, int $month)
    {
        $startOf = now()->month == $month ?
            now()->startOfDay()->toDateTimeString() :
            now()->month($month)->startOfMonth()->startOfDay()->toDateTimeString();

        $query->whereBetween('start_date', [
            $startOf,
            now()->month($month)->endOfMonth()->endOfDay()->toDateTimeString()
        ]);
    }

    public function scopeWherePickupStation($query, ?Station $station)
    {
        if($station){
            $query->wherePickupStationId($station->id);
        }
    }

}
