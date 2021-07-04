<?php

use App\Order;
use App\OrderItem;
use App\Station;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\User::class)->create(['email'=>'roadsurfer@gmail.com', 'password'=> Hash::make('secret')]);

        $stations = $this->createStations();

        // And the first station is the default
        Station::first()->update(['default'=>1]);

        // And lets says we have only 4 type of equipments
        $itemNames = ['Bed', 'Toilet', 'Bed sheets', 'Shower'];
        foreach($itemNames as $itemName){
            factory(\App\Item::class)->create(['type'=>'equipment', 'name'=>$itemName]);
        }

        // Lets bind this equipments to all 5 stations first, And give it a random amount
        $stations->each(function(Station $station){
            \App\Item::all()->each(function($item) use($station){
                $station->items()->save($item, ['amount'=>random_int(5, 20)]);
            });

            // Now lets create couple of orders
            $firstOrder = factory(Order::class)->create([
                'start_date'=>now()->toDateTimeString(),
                'end_date'=>now()->addDays(5)->toDateTimeString(),
                'pickup_station_id'=>$station->id,
                'drop_off_station_id'=>$station->id
            ]);

            $secondOrder = factory(Order::class)->create([
                'start_date'=>now()->addDays(3)->toDateTimeString(),
                'end_date'=>now()->addDays(7)->toDateTimeString(),
                'pickup_station_id'=>$station->id,
                'drop_off_station_id'=>$station->id
            ]);

            $thirdOrder = factory(Order::class)->create([
                'start_date'=>now()->addDays(5)->toDateTimeString(),
                'end_date'=>now()->addDays(10)->toDateTimeString(),
                'pickup_station_id'=>$station->id,
                'drop_off_station_id'=>$station->id
            ]);

            $fourthOrder = factory(Order::class)->create([
                'start_date'=>now()->addDays(1)->toDateTimeString(),
                'end_date'=>now()->addDays(18)->toDateTimeString(),
                'pickup_station_id'=>$station->id,
                'drop_off_station_id'=>$station->id
            ]);

            $fifthOrder = factory(Order::class)->create([
                'start_date'=>now()->addDays(15)->toDateTimeString(),
                'end_date'=>now()->addDays(27)->toDateTimeString(),
                'pickup_station_id'=>$station->id,
                'drop_off_station_id'=>$station->id
            ]);


            $this->createOrderItem($firstOrder);
            $this->createOrderItem($secondOrder);
            $this->createOrderItem($thirdOrder);
            $this->createOrderItem($fourthOrder);
            $this->createOrderItem($fifthOrder);

        });

    }

    private function createOrderItem(Order $order)
    {
        $item = \App\Item::inRandomOrder()->first();
        factory(OrderItem::class)->create([
            'item_id'=>$item->id,
            'order_id'=>$order->id,
            'item_name'=>$item->name,
            'quantity'=>1
        ]);

        $item = \App\Item::where('id', '<>', $item->id)->first();
        factory(OrderItem::class)->create([
            'item_id'=>$item->id,
            'order_id'=>$order->id,
            'item_name'=>$item->name,
            'quantity'=>1
        ]);
    }

    private function createStations()
    {
        $cities = ['Berlin', 'Hamburg', 'Munich', 'Saxony', 'Bremen', 'Stuttgart', 'Cologne', 'Bonn'];

        foreach($cities as $city){
            // Lets say we have Five stations
            factory(\App\Station::class)->create(['name'=>$city]);
        }

        return Station::all();
    }
}
