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

    public function totalItems(int $itemId, string $dayDate): int
    {
        $totalAmount = self::items()->where('items.id', $itemId)->first()->pivot->amount;

        $droppedOfItems = 0;
        Order::where('drop_off_station_id', $this->id)
            ->whereDate('end_date', $dayDate)
            ->whereHas('orderItems.item', function($query) use($itemId){
                $query->where('id', $itemId);
            })
            ->get()->each(function(Order $order) use(&$droppedOfItems, $itemId){
                $droppedOfItems += $order->orderItems()->where('item_id', $itemId)->first()->quantity;
            });

        // Update the totalAmount of that equipment for that station
        if($droppedOfItems > 0){
            self::items()->where('items.id', $itemId)->first()->pivot->update(['amount'=>$totalAmount + $droppedOfItems]);
        }

        return $totalAmount;
    }
}
