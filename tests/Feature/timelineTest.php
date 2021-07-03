<?php

namespace Tests\Feature;

use App\Item;
use App\ItemMeta;
use App\Http\Controllers\TimelineController;
use App\Order;
use App\OrderItem;
use App\Station;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class timelineTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @see TimelineController::index()
     */
    public function testTimelineExists()
    {
        $this->withoutExceptionHandling();
        $this->get('/timeline')->assertStatus(200);
    }

    /**
     * @see TimelineController::index()
     */
    public function testTimelineReturnsTimeLineObject()
    {
        $this->withoutExceptionHandling();
        $this->get('/timeline')->assertViewHas('timeline');
    }

    /**
     * @see TimelineController::index()
     */
    public function testTimelineReturnsTimeLineObjectWhichContains1MonthElements()
    {
        $this->withoutExceptionHandling();
        $timeline = [];
        for($i = 0; $i < now()->daysInMonth; $i++){
            $timeline[] = [
                'date'=>now()->addDays($i)->toDateTimeString(),
                'day'=>$i + 1
            ];
        }

        $this->get('/timeline')
            ->assertViewHas('timeline', $timeline);
    }

    /**
     * @see TimelineController::index()
     */
    public function testTimelineReturnsTotalNumberOfOneItemThatHasBookedForToday()
    {
        $this->withoutExceptionHandling();
        // Create fake data
        $item = factory(Item::class)->create(['type'=>'equipment']);
        $pickupStation = factory(Station::class)->create(['default'=>1]);
        $pickupStation->items()->save($item, ['amount'=>3]);
        $dropOffStation = factory(Station::class)->create();

        $order = factory(Order::class)->create([
            'start_date'=>now()->startOfDay()->toDateTimeString(),
            'end_date'=>now()->addDay()->startOfDay()->toDateTimeString(),
            'pickup_station_id'=>$pickupStation->id,
            'drop_off_station_id'=>$dropOffStation->id
            ]);

        $orderItem = factory(OrderItem::class)->create([
            'item_id'=>$item->id,
            'order_id'=>$order->id,
            'item_name'=>$item->name,
            'quantity'=>1
        ]);


        // Build the expectation
        $timeline[] = [
            'items'=>[
                ['item_id'=>$orderItem->item_id, 'item_name'=>$orderItem->item_name, 'booked'=>1]
            ],
            'date'=>now()->toDateString(),
        ];

        // call timeline route
        $this->get('/timeline')->assertViewHas('timeline', $timeline);
    }

    /**
     * @see TimelineController::index()
     */
    public function testTimelineReturnsTotalNumberOfItemsThatHasBookedForToday()
    {
        $this->withoutExceptionHandling();
        // Create fake data
        $firstItem = factory(Item::class)->create(['type'=>'equipment']);
        $secondItem = factory(Item::class)->create(['type'=>'equipment']);
        $pickupStation = factory(Station::class)->create(['default'=>1]);
        $pickupStation->items()->save($firstItem, ['amount'=>3]);
        $pickupStation->items()->save($secondItem, ['amount'=>5]);
        $dropOffStation = factory(Station::class)->create();

        $order = factory(Order::class)->create([
            'start_date'=>now()->startOfDay()->toDateTimeString(),
            'end_date'=>now()->addDay()->startOfDay()->toDateTimeString(),
            'pickup_station_id'=>$pickupStation->id,
            'drop_off_station_id'=>$dropOffStation->id
        ]);

        $firstOrderItem = factory(OrderItem::class)->create([
            'item_id'=>$firstItem->id,
            'order_id'=>$order->id,
            'item_name'=>$firstItem->name,
            'quantity'=>1
        ]);

        $secondOrderItem = factory(OrderItem::class)->create([
            'item_id'=>$secondItem->id,
            'order_id'=>$order->id,
            'item_name'=>$secondItem->name,
            'quantity'=>2
        ]);


        // Build the expectation
        $timeline[] = [
            'items'=>[
                ['item_id'=>$firstOrderItem->item_id, 'item_name'=>$firstOrderItem->item_name, 'booked'=>1],
                ['item_id'=>$secondOrderItem->item_id, 'item_name'=>$secondOrderItem->item_name, 'booked'=>2]
            ],
            'date'=>now()->toDateString(),
        ];

        // call timeline route
        $this->get('/timeline')->assertViewHas('timeline', $timeline);
    }


    /**
     * @see TimelineController::index()
     */
    public function testTimelineReturnsTotalNumberOfItemsThatHasBookedOnDifferentOrders()
    {
        $this->withoutExceptionHandling();
        // Create fake data
        $firstItem = factory(Item::class)->create(['type'=>'equipment']);
        $secondItem = factory(Item::class)->create(['type'=>'equipment']);
        $pickupStation = factory(Station::class)->create(['default'=>1]);
        $pickupStation->items()->save($firstItem, ['amount'=>3]);
        $pickupStation->items()->save($secondItem, ['amount'=>5]);
        $dropOffStation = factory(Station::class)->create();

        $firstOrder = factory(Order::class)->create([
            'start_date'=>now()->startOfDay()->toDateTimeString(),
            'end_date'=>now()->addDay()->startOfDay()->toDateTimeString(),
            'pickup_station_id'=>$pickupStation->id,
            'drop_off_station_id'=>$dropOffStation->id
        ]);

        $firstOrderItem = factory(OrderItem::class)->create([
            'item_id'=>$firstItem->id,
            'order_id'=>$firstOrder->id,
            'item_name'=>$firstItem->name,
            'quantity'=>1
        ]);

        $secondOrder = factory(Order::class)->create([
            'start_date'=>now()->startOfDay()->toDateTimeString(),
            'end_date'=>now()->addDay()->startOfDay()->toDateTimeString(),
            'pickup_station_id'=>$pickupStation->id,
            'drop_off_station_id'=>$dropOffStation->id
        ]);

        $secondOrderItem = factory(OrderItem::class)->create([
            'item_id'=>$secondItem->id,
            'order_id'=>$secondOrder->id,
            'item_name'=>$secondItem->name,
            'quantity'=>2
        ]);


        // Build the expectation
        $timeline[] = [
            'items'=>[
                ['item_id'=>$firstOrderItem->item_id, 'item_name'=>$firstOrderItem->item_name, 'booked'=>1],
                ['item_id'=>$secondOrderItem->item_id, 'item_name'=>$secondOrderItem->item_name, 'booked'=>2]
            ],
            'date'=>now()->toDateString(),
        ];

        // call timeline route
        $this->get('/timeline')->assertViewHas('timeline', $timeline);
    }

    /**
     * @see TimelineController::index()
     */
    public function testTimelineReturnsTotalNumberOfItemsThatHasBookedOnDifferentDates()
    {
        $this->withoutExceptionHandling();
        // Create fake data
        $firstItem = factory(Item::class)->create(['type'=>'equipment']);
        $secondItem = factory(Item::class)->create(['type'=>'equipment']);
        $pickupStation = factory(Station::class)->create(['default'=>1]);
        $pickupStation->items()->save($firstItem, ['amount'=>3]);
        $pickupStation->items()->save($secondItem, ['amount'=>5]);
        $dropOffStation = factory(Station::class)->create();

        $firstOrder = factory(Order::class)->create([
            'start_date'=>now()->startOfDay()->toDateTimeString(),
            'end_date'=>now()->addDay()->startOfDay()->toDateTimeString(),
            'pickup_station_id'=>$pickupStation->id,
            'drop_off_station_id'=>$dropOffStation->id
        ]);

        $firstOrderItem = factory(OrderItem::class)->create([
            'item_id'=>$firstItem->id,
            'order_id'=>$firstOrder->id,
            'item_name'=>$firstItem->name,
            'quantity'=>1
        ]);

        $secondOrder = factory(Order::class)->create([
            'start_date'=>now()->addDays(2)->startOfDay()->toDateTimeString(),
            'end_date'=>now()->addDays(3)->startOfDay()->toDateTimeString(),
            'pickup_station_id'=>$pickupStation->id,
            'drop_off_station_id'=>$dropOffStation->id
        ]);

        $secondOrderItem = factory(OrderItem::class)->create([
            'item_id'=>$secondItem->id,
            'order_id'=>$secondOrder->id,
            'item_name'=>$secondItem->name,
            'quantity'=>2
        ]);


        // Build the expectation
        $timeline = [
            [
                'items'=>[
                    ['item_id'=>$firstOrderItem->item_id, 'item_name'=>$firstOrderItem->item_name, 'booked'=>1],
                ],
                'date'=>now()->toDateString(),
            ]
            ,[
                'items'=>[
                    ['item_id'=>$secondOrderItem->item_id, 'item_name'=>$secondOrderItem->item_name, 'booked'=>2]
                ],
                'date'=>now()->addDays(2)->toDateString(),
            ]
        ];

        // call timeline route
        $this->get('/timeline')->assertViewHas('timeline', $timeline);
    }

    /**
     * @see TimelineController::index()
     */
    public function testTimelineReturnsTotalNumberOfItemThatHasBookedOnConsecutiveDays()
    {
        $this->withoutExceptionHandling();
        // Create fake data
        $firstItem = factory(Item::class)->create(['type'=>'equipment']);
        $pickupStation = factory(Station::class)->create(['default'=>1]);
        $pickupStation->items()->save($firstItem, ['amount'=>8]);
        $dropOffStation = factory(Station::class)->create();

        $firstOrder = factory(Order::class)->create([
            'start_date'=>now()->startOfDay()->toDateTimeString(),
            'end_date'=>now()->addDays(3)->startOfDay()->toDateTimeString(),
            'pickup_station_id'=>$pickupStation->id,
            'drop_off_station_id'=>$dropOffStation->id
        ]);

        $firstOrderItem = factory(OrderItem::class)->create([
            'item_id'=>$firstItem->id,
            'order_id'=>$firstOrder->id,
            'item_name'=>$firstItem->name,
            'quantity'=>1
        ]);

        $secondOrder = factory(Order::class)->create([
            'start_date'=>now()->addDays(1)->startOfDay()->toDateTimeString(),
            'end_date'=>now()->addDays(3)->startOfDay()->toDateTimeString(),
            'pickup_station_id'=>$pickupStation->id,
            'drop_off_station_id'=>$dropOffStation->id
        ]);

        $secondOrderItem = factory(OrderItem::class)->create([
            'item_id'=>$firstItem->id,
            'order_id'=>$secondOrder->id,
            'item_name'=>$firstItem->name,
            'quantity'=>2
        ]);


        // Build the expectation
        $timeline = [
            [
                'items'=>[
                    ['item_id'=>$firstOrderItem->item_id, 'item_name'=>$firstOrderItem->item_name, 'booked'=>1],
                ],
                'date'=>now()->toDateString(),
            ],
            [
                'items'=>[
                    ['item_id'=>$secondOrderItem->item_id, 'item_name'=>$secondOrderItem->item_name, 'booked'=>3]
                ],
                'date'=>now()->addDays(1)->toDateString(),
            ],
            [
                'items'=>[
                    ['item_id'=>$secondOrderItem->item_id, 'item_name'=>$secondOrderItem->item_name, 'booked'=>3]
                ],
                'date'=>now()->addDays(2)->toDateString(),
            ]
        ];

        // call timeline route
        $this->get('/timeline')->assertViewHas('timeline', $timeline);
    }


    /**
     * @see TimelineController::index()
     */
    public function testTimelineReturnsTotalNumberOfAvailItemThatHasBookedToday()
    {
        $this->withoutExceptionHandling();
        // Create fake data
        $firstItem = factory(Item::class)->create(['type'=>'equipment']);
        $pickupStation = factory(Station::class)->create(['default'=>1]);
        $pickupStation->items()->save($firstItem, ['amount'=>8]);
        $dropOffStation = factory(Station::class)->create();

        $firstOrder = factory(Order::class)->create([
            'start_date'=>now()->startOfDay()->toDateTimeString(),
            'end_date'=>now()->addDays(1)->startOfDay()->toDateTimeString(),
            'pickup_station_id'=>$pickupStation->id,
            'drop_off_station_id'=>$dropOffStation->id
        ]);

        $firstOrderItem = factory(OrderItem::class)->create([
            'item_id'=>$firstItem->id,
            'order_id'=>$firstOrder->id,
            'item_name'=>$firstItem->name,
            'quantity'=>1
        ]);


        // Build the expectation
        $timeline = [
            [
                'items'=>[
                    ['item_id'=>$firstOrderItem->item_id, 'item_name'=>$firstOrderItem->item_name, 'booked'=>1, 'available'=>7],
                ],
                'date'=>now()->toDateString(),
            ]
        ];

        // call timeline route
        $this->get('/timeline')->assertViewHas('timeline', $timeline);
    }

    /**
     * @see TimelineController::index()
     */
    public function testTimelineShouldNotReturnsNextMonthTimeline()
    {
        $this->withoutExceptionHandling();
        // Create fake data
        $firstItem = factory(Item::class)->create(['type'=>'equipment']);
        $pickupStation = factory(Station::class)->create(['default'=>1]);
        $pickupStation->items()->save($firstItem, ['amount'=>40]);
        $dropOffStation = factory(Station::class)->create();

        $startOfMonth = now()->toDateTimeString();
        $endOfMonth = now()->addDays(45)->startOfDay()->toDateTimeString();
        $firstOrder = factory(Order::class)->create([
            'start_date'=>$startOfMonth,
            'end_date'=>$endOfMonth,
            'pickup_station_id'=>$pickupStation->id,
            'drop_off_station_id'=>$dropOffStation->id
        ]);

        $firstOrderItem = factory(OrderItem::class)->create([
            'item_id'=>$firstItem->id,
            'order_id'=>$firstOrder->id,
            'item_name'=>$firstItem->name,
            'quantity'=>1
        ]);


        // add empty items
        for($i = now()->startOfMonth()->day; $i < Carbon::parse($startOfMonth)->day; $i++){
            $timeline[] = [
                'items'=>[],
                'date'=>now()->startOfMonth()->addDays($i - 1)->toDateString(),
            ];
        }

        // Build the expectation
        for($i = now()->day; $i <= now()->endOfMonth()->day; $i ++){
            $timeline[] = [
                'items'=>[
                    ['item_id'=>$firstOrderItem->item_id, 'item_name'=>$firstOrderItem->item_name, 'booked'=>1, 'available'=>39],
                ],
                'date'=>now()->day($i)->toDateString(),
            ];
        }

        // call timeline route
        $this->get('/timeline')->assertViewHas('timeline', $timeline);
    }
}
