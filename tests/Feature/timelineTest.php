<?php

namespace Tests\Feature;

use App\Item;
use App\Http\Controllers\TimelineController;
use App\Order;
use App\OrderItem;
use App\Station;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class timelineTest extends TestCase
{
    use DatabaseMigrations;

    protected $firstItem;

    protected $secondItem;

    protected $pickupStation;

    public function setUp(): void
    {
        parent::setUp();
        $this->be(factory(User::class)->create());

        $this->firstItem = factory(Item::class)->create(['type'=>'equipment']);
        $this->secondItem = factory(Item::class)->create(['type'=>'equipment']);
        $this->pickupStation = factory(Station::class)->create(['default'=>1]);
    }

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
        $timeline = [];
        $timeline = $this->buildTheRestOfTheDays(1, now()->daysInMonth, $timeline);

        $this->get('/timeline')
            ->assertViewHas('timeline', $timeline);
    }

    /**
     * @see TimelineController::index()
     */
    public function testTimelineReturnsTotalNumberOfOneItemThatHasBookedForToday()
    {
        // Create fake data

        $this->pickupStation->items()->save($this->firstItem, ['amount'=>3]);
        $dropOffStation = factory(Station::class)->create();

        $order = factory(Order::class)->create([
            'start_date'=>now()->startOfDay()->toDateTimeString(),
            'end_date'=>now()->addDay()->startOfDay()->toDateTimeString(),
            'pickup_station_id'=>$this->pickupStation->id,
            'drop_off_station_id'=>$dropOffStation->id
            ]);

        $orderItem = factory(OrderItem::class)->create([
            'item_id'=>$this->firstItem->id,
            'order_id'=>$order->id,
            'item_name'=>$this->firstItem->name,
            'quantity'=>1
        ]);

        $timeline = [];
        // Build the rest of the days
        $timeline = $this->buildTheRestOfTheDays(now()->startOfMonth()->day, now()->subDay()->day, $timeline);

        // Build the expectation
        $timeline[] = [
            'items'=>[
                ['item_id'=>$orderItem->item_id, 'item_name'=>$orderItem->item_name, 'booked'=>1, 'available'=>2]
            ],
            'date'=>now()->toDateString(),
        ];

        // Build the rest of the days
        $timeline = $this->buildTheRestOfTheDays(now()->addDays(1)->day, now()->endOfMonth()->day, $timeline);

        // call timeline route
        $this->get('/timeline')->assertViewHas('timeline', $timeline);
    }

    /**
     * @see TimelineController::index()
     */
    public function testTimelineReturnsTotalNumberOfItemsThatHasBookedForToday()
    {

        $this->pickupStation->items()->save($this->firstItem, ['amount'=>3]);
        $this->pickupStation->items()->save($this->secondItem, ['amount'=>5]);
        $dropOffStation = factory(Station::class)->create();

        $order = factory(Order::class)->create([
            'start_date'=>now()->startOfDay()->toDateTimeString(),
            'end_date'=>now()->addDay()->startOfDay()->toDateTimeString(),
            'pickup_station_id'=>$this->pickupStation->id,
            'drop_off_station_id'=>$dropOffStation->id
        ]);

        $firstOrderItem = factory(OrderItem::class)->create([
            'item_id'=>$this->firstItem->id,
            'order_id'=>$order->id,
            'item_name'=>$this->firstItem->name,
            'quantity'=>1
        ]);

        $secondOrderItem = factory(OrderItem::class)->create([
            'item_id'=>$this->secondItem->id,
            'order_id'=>$order->id,
            'item_name'=>$this->secondItem->name,
            'quantity'=>2
        ]);

        $timeline = [];
        // Build the rest of the days
        $timeline = $this->buildTheRestOfTheDays(now()->startOfMonth()->day, now()->subDay()->day, $timeline);

        // Build the expectation
        $timeline[] = [
            'items'=>[
                ['item_id'=>$firstOrderItem->item_id, 'item_name'=>$firstOrderItem->item_name, 'booked'=>1, 'available'=>2],
                ['item_id'=>$secondOrderItem->item_id, 'item_name'=>$secondOrderItem->item_name, 'booked'=>2, 'available'=>3]
            ],
            'date'=>now()->toDateString(),
        ];

        $timeline = $this->buildTheRestOfTheDays(now()->addDays(1)->day, now()->endOfMonth()->day, $timeline);

        // call timeline route
        $this->get('/timeline')->assertViewHas('timeline', $timeline);
    }


    /**
     * @see TimelineController::index()
     */
    public function testTimelineReturnsTotalNumberOfItemsThatHasBookedOnDifferentOrders()
    {
        // Create fake data
        $this->pickupStation->items()->save($this->firstItem, ['amount'=>3]);
        $this->pickupStation->items()->save($this->secondItem, ['amount'=>5]);
        $dropOffStation = factory(Station::class)->create();

        $firstOrder = factory(Order::class)->create([
            'start_date'=>now()->startOfDay()->toDateTimeString(),
            'end_date'=>now()->addDay()->startOfDay()->toDateTimeString(),
            'pickup_station_id'=>$this->pickupStation->id,
            'drop_off_station_id'=>$dropOffStation->id
        ]);

        $firstOrderItem = factory(OrderItem::class)->create([
            'item_id'=>$this->firstItem->id,
            'order_id'=>$firstOrder->id,
            'item_name'=>$this->firstItem->name,
            'quantity'=>1
        ]);

        $secondOrder = factory(Order::class)->create([
            'start_date'=>now()->startOfDay()->toDateTimeString(),
            'end_date'=>now()->addDay()->startOfDay()->toDateTimeString(),
            'pickup_station_id'=>$this->pickupStation->id,
            'drop_off_station_id'=>$dropOffStation->id
        ]);

        $secondOrderItem = factory(OrderItem::class)->create([
            'item_id'=>$this->secondItem->id,
            'order_id'=>$secondOrder->id,
            'item_name'=>$this->secondItem->name,
            'quantity'=>2
        ]);


        $timeline = [];
        // Build the rest of the days
        $timeline = $this->buildTheRestOfTheDays(now()->startOfMonth()->day, now()->subDay()->day, $timeline);

        // Build the expectation
        $timeline[] = [
            'items'=>[
                ['item_id'=>$firstOrderItem->item_id, 'item_name'=>$firstOrderItem->item_name, 'booked'=>1, 'available'=>2],
                ['item_id'=>$secondOrderItem->item_id, 'item_name'=>$secondOrderItem->item_name, 'booked'=>2, 'available'=>3]
            ],
            'date'=>now()->toDateString(),
        ];

        $timeline = $this->buildTheRestOfTheDays(now()->addDays(1)->day, now()->endOfMonth()->day, $timeline);

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
        $this->secondItem = factory(Item::class)->create(['type'=>'equipment']);
        $this->pickupStation->items()->save($this->firstItem, ['amount'=>3]);
        $this->pickupStation->items()->save($this->secondItem, ['amount'=>5]);
        $dropOffStation = factory(Station::class)->create();

        $firstOrder = factory(Order::class)->create([
            'start_date'=>now()->startOfDay()->toDateTimeString(),
            'end_date'=>now()->addDay()->startOfDay()->toDateTimeString(),
            'pickup_station_id'=>$this->pickupStation->id,
            'drop_off_station_id'=>$dropOffStation->id
        ]);

        $firstOrderItem = factory(OrderItem::class)->create([
            'item_id'=>$this->firstItem->id,
            'order_id'=>$firstOrder->id,
            'item_name'=>$this->firstItem->name,
            'quantity'=>1
        ]);

        $secondOrder = factory(Order::class)->create([
            'start_date'=>now()->addDays(2)->startOfDay()->toDateTimeString(),
            'end_date'=>now()->addDays(3)->startOfDay()->toDateTimeString(),
            'pickup_station_id'=>$this->pickupStation->id,
            'drop_off_station_id'=>$dropOffStation->id
        ]);

        $secondOrderItem = factory(OrderItem::class)->create([
            'item_id'=>$this->secondItem->id,
            'order_id'=>$secondOrder->id,
            'item_name'=>$this->secondItem->name,
            'quantity'=>2
        ]);

        $timeline = [];
        // Build the rest of the days
        $timeline = $this->buildTheRestOfTheDays(now()->startOfMonth()->day, now()->subDay()->day, $timeline);

        // Build the expectation
        $timeline = array_merge($timeline, [
            [
                'items'=>[
                    ['item_id'=>$firstOrderItem->item_id, 'item_name'=>$firstOrderItem->item_name, 'booked'=>1, 'available'=>2],
                ],
                'date'=>now()->toDateString(),
            ],
            [
                'items'=>[],
                'date'=>now()->addDays(1)->toDateString(),
            ]
            ,[
                'items'=>[
                    ['item_id'=>$secondOrderItem->item_id, 'item_name'=>$secondOrderItem->item_name, 'booked'=>2, 'available'=>3]
                ],
                'date'=>now()->addDays(2)->toDateString(),
            ]
        ]);

        $timeline = $this->buildTheRestOfTheDays(now()->addDays(3)->day, now()->endOfMonth()->day, $timeline);

        // call timeline route
        $this->get('/timeline')->assertViewHas('timeline', $timeline);
    }

    /**
     * @see TimelineController::index()
     */
    public function testTimelineReturnsTotalNumberOfItemThatHasBookedOnConsecutiveDays()
    {

        // Create fake data
        $this->pickupStation->items()->save($this->firstItem, ['amount'=>8]);
        $dropOffStation = factory(Station::class)->create();

        $firstOrder = factory(Order::class)->create([
            'start_date'=>now()->startOfDay()->toDateTimeString(),
            'end_date'=>now()->addDays(3)->startOfDay()->toDateTimeString(),
            'pickup_station_id'=>$this->pickupStation->id,
            'drop_off_station_id'=>$dropOffStation->id
        ]);

        $firstOrderItem = factory(OrderItem::class)->create([
            'item_id'=>$this->firstItem->id,
            'order_id'=>$firstOrder->id,
            'item_name'=>$this->firstItem->name,
            'quantity'=>1
        ]);

        $secondOrder = factory(Order::class)->create([
            'start_date'=>now()->addDays(1)->startOfDay()->toDateTimeString(),
            'end_date'=>now()->addDays(3)->startOfDay()->toDateTimeString(),
            'pickup_station_id'=>$this->pickupStation->id,
            'drop_off_station_id'=>$dropOffStation->id
        ]);

        $secondOrderItem = factory(OrderItem::class)->create([
            'item_id'=>$this->firstItem->id,
            'order_id'=>$secondOrder->id,
            'item_name'=>$this->firstItem->name,
            'quantity'=>2
        ]);

        $timeline = [];
        // Build the rest of the days
        $timeline = $this->buildTheRestOfTheDays(now()->startOfMonth()->day, now()->subDay()->day, $timeline);

        // Build the expectation
        $timeline = array_merge($timeline, [
            [
                'items'=>[
                    ['item_id'=>$firstOrderItem->item_id, 'item_name'=>$firstOrderItem->item_name, 'booked'=>1, 'available'=>7],
                ],
                'date'=>now()->toDateString(),
            ],
            [
                'items'=>[
                    ['item_id'=>$secondOrderItem->item_id, 'item_name'=>$secondOrderItem->item_name, 'booked'=>3, 'available'=>5]
                ],
                'date'=>now()->addDays(1)->toDateString(),
            ],
            [
                'items'=>[
                    ['item_id'=>$secondOrderItem->item_id, 'item_name'=>$secondOrderItem->item_name, 'booked'=>3, 'available'=>5]
                ],
                'date'=>now()->addDays(2)->toDateString(),
            ]
        ]);

        $timeline = $this->buildTheRestOfTheDays(now()->addDays(3)->day, now()->endOfMonth()->day, $timeline);

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
        $this->pickupStation->items()->save($this->firstItem, ['amount'=>8]);
        $dropOffStation = factory(Station::class)->create();

        $firstOrder = factory(Order::class)->create([
            'start_date'=>now()->startOfDay()->toDateTimeString(),
            'end_date'=>now()->addDays(1)->startOfDay()->toDateTimeString(),
            'pickup_station_id'=>$this->pickupStation->id,
            'drop_off_station_id'=>$dropOffStation->id
        ]);

        $firstOrderItem = factory(OrderItem::class)->create([
            'item_id'=>$this->firstItem->id,
            'order_id'=>$firstOrder->id,
            'item_name'=>$this->firstItem->name,
            'quantity'=>1
        ]);

        $timeline = [];
        // Build the rest of the days
        $timeline = $this->buildTheRestOfTheDays(now()->startOfMonth()->day, now()->subDay()->day, $timeline);

        // Build the expectation
        $timeline = array_merge($timeline, [
            [
                'items'=>[
                    ['item_id'=>$firstOrderItem->item_id, 'item_name'=>$firstOrderItem->item_name, 'booked'=>1, 'available'=>7],
                ],
                'date'=>now()->toDateString(),
            ]
        ]);

        $timeline = $this->buildTheRestOfTheDays(now()->addDays(1)->day, now()->endOfMonth()->day, $timeline);

        // call timeline route
        $this->get('/timeline')->assertViewHas('timeline', $timeline);
    }

    /**
     * @see TimelineController::index()
     */
    public function testTimelineShouldNotReturnsNextMonthTimeline()
    {
        // Create fake data
        $this->pickupStation->items()->save($this->firstItem, ['amount'=>40]);
        $dropOffStation = factory(Station::class)->create();

        $startOfMonth = now()->toDateTimeString();
        $endOfMonth = now()->addDays(45)->startOfDay()->toDateTimeString();
        $firstOrder = factory(Order::class)->create([
            'start_date'=>$startOfMonth,
            'end_date'=>$endOfMonth,
            'pickup_station_id'=>$this->pickupStation->id,
            'drop_off_station_id'=>$dropOffStation->id
        ]);

        $firstOrderItem = factory(OrderItem::class)->create([
            'item_id'=>$this->firstItem->id,
            'order_id'=>$firstOrder->id,
            'item_name'=>$this->firstItem->name,
            'quantity'=>1
        ]);


        $timeline = [];

        // Build the rest of the days
        $timeline = $this->buildTheRestOfTheDays(now()->startOfMonth()->day, now()->subDays(1)->day, $timeline);

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

    /**
     * @see TimelineController::index()
     */
    public function testTimelineReturnsMonthAsKeyInResponse()
    {

        // call timeline route
        $this->get('/timeline')->assertViewHas('month');
    }

    /**
     * @see TimelineController::index()
     */
    public function testTimelineApiShouldReturnJsonResponse()
    {

        // Build the expectation
        for($i = now()->startOfMonth()->day; $i <= now()->endOfMonth()->day; $i ++){
            $timeline[] = [
                'date'=>now()->day($i)->toDateString(),
                'items'=>[],
            ];
        }

        // call timeline route
        $this->get('/timeline', ['HTTP_X-Requested-With' => 'XMLHttpRequest'])
            ->assertJson(['data'=>$timeline]);
    }

    /**
     * @see TimelineController::index()
     */
    public function testTimelineReturnsMonthAsCurrentMonth()
    {
        // call timeline route
        $this->get('/timeline')->assertViewHas('month', now()->month);
    }

    /**
     * @see TimelineController::index()
     */
    public function testTimelineReturnsMonthAsTheRequestedMonth()
    {
        // call timeline route
        $this->get('/timeline?month='.now()->addMonths(1)->month)->assertViewHas('month', now()->addMonths(1)->month);
    }

    /**
     * @see TimelineController::index()
     */
    public function testTimelineReturnsDefaultStation()
    {

        // call timeline route
        $this->get('/timeline')->assertViewHas('station', $this->pickupStation);
    }

    /**
     * @see TimelineController::index()
     */
    public function testTimelineReturnsStationAsRequestedStation()
    {
        $this->withoutExceptionHandling();

        $station = factory(Station::class)->create();

        // call timeline route
        $this->get('/timeline?station='.$station->id)->assertViewHas('station', $station);
    }

    public function testTimelineAvailableShouldDeductedIfThePickupStationWasNotAsDropOffStation()
    {
        $this->withoutExceptionHandling();

        $this->pickupStation->items()->save($this->firstItem, ['amount'=>40]);

        $dropOffStation = factory(Station::class)->create(['default'=>1]);
        $dropOffStation->items()->save($this->firstItem, ['amount'=>1]);

        $firstOrder = factory(Order::class)->create([
            'start_date'=>now()->toDateTimeString(),
            'end_date'=>now()->addDays(1)->toDateTimeString(),
            'pickup_station_id'=>$this->pickupStation->id,
            'drop_off_station_id'=>$dropOffStation->id
        ]);

        $firstOrderItem = factory(OrderItem::class)->create([
            'item_id'=>$this->firstItem->id,
            'order_id'=>$firstOrder->id,
            'item_name'=>$this->firstItem->name
        ]);

        $secondOrder = factory(Order::class)->create([
            'start_date'=>now()->toDateTimeString(),
            'end_date'=>now()->addDays(4)->toDateTimeString(),
            'pickup_station_id'=>$dropOffStation->id,
            'drop_off_station_id'=>$dropOffStation->id
        ]);

        $secondOrderItem = factory(OrderItem::class)->create([
            'item_id'=>$this->firstItem->id,
            'order_id'=>$secondOrder->id,
            'item_name'=>$this->firstItem->name
        ]);

        $timeline = [];
        // Build the rest of the days
        $timeline = $this->buildTheRestOfTheDays(now()->startOfMonth()->day, now()->subDay()->day, $timeline);

        // Build the expectation
        $timeline = array_merge($timeline, [
            [
                'items'=>[$this->buildItem($firstOrderItem, 1, 0)],
                'date'=>now()->toDateString(),
            ],
            [
                'items'=>[$this->buildItem($firstOrderItem, 1, 0)],
                'date'=>now()->addDays(1)->toDateString(),
            ],
            [
                'items'=>[$this->buildItem($firstOrderItem)],
                'date'=>now()->addDays(2)->toDateString(),
            ],
            [
                'items'=>[$this->buildItem($firstOrderItem)],
                'date'=>now()->addDays(3)->toDateString(),
            ]
        ]);

        $timeline = $this->buildTheRestOfTheDays(now()->addDays(4)->day, now()->endOfMonth()->day, $timeline);

        $this->get('/timeline?station='.$dropOffStation->id)->assertViewHas('timeline', $timeline);
    }


    private function buildTheRestOfTheDays($startDay, $endDay, $timeline)
    {
        for($i = $startDay; $i <= $endDay; $i++){
            $timeline[] = [
                'items'=>[],
                'date'=>now()->startOfMonth()->addDays($i - 1)->toDateString(),
            ];
        }

        return $timeline;
    }

    private function buildItem(OrderItem $orderItem, int $booked = 1, int $available = 1): array
    {
        return ['item_id'=>$orderItem->item_id, 'item_name'=>$orderItem->item_name, 'booked'=>$booked, 'available'=>$available];
    }
}
