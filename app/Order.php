<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeSelectOneMonth($query, ?int $month)
    {
        $startDate = $month == null ?
            now()->startOfDay()->toDateTimeString() :
            now()->addMonths($month)->startOfMonth()->startOfDay()->toDateTimeString();


        $query->whereBetween('start_date', [$startDate, now()->addMonths($month)->endOfMonth()->endOfDay()->toDateTimeString()]);
    }

    public function scopeWherePickupStation($query, ?Station $station)
    {
        if($station){
            $query->wherePickupStationId($station->id);
        }
    }

}
