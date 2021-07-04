<?php

namespace App\Http\Controllers;

use App\Order;
use App\OrderItem;
use App\Station;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class TimelineController extends Controller
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @var Station
     */
    private $station;

    /**
     * TimelineController constructor.
     * @param Order $order
     * @param Station $station
     */
    public function __construct(Order $order, Station $station)
    {
        $this->order = $order;
        $this->station = $station;
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $month = $request->has('month') ? $request->month : now()->month;
        $station = $request->has('station') ?
            $this->station->where('id', $request->station)->first() :
            $this->station->default()->first();

        $timeline = [];

         $this->order
            ->selectOneMonth($month)
            ->wherePickupStation($station)
            ->orderBy('start_date')
            ->chunk(50, function($orders) use(&$timeline, &$station){
                $orders->each(function ($order) use (&$station, &$timeline) {
                    $bookedDays = Carbon::parse($order->start_date)->diffInDays($order->end_date);

                    $order->orderItems->each(function ($orderItem) use (&$bookedDays, &$order, &$station, &$timeline) {

                        for ($day = 0; $day < $bookedDays; $day++) {
                            $dayDate = Carbon::parse($order->start_date)->addDays($day)->toDateString();

                            $totalItems = $station->totalItems($orderItem->item_id, $dayDate);

                            if(Carbon::parse($dayDate)->isNextMonth()){
                                continue;
                            }

                            $dateFoundInTimeline = findValueInArray($dayDate, $timeline, 'date');
                            if($dateFoundInTimeline !== false){
                                $items = $timeline[$dateFoundInTimeline]['items'];
                                $itemFoundInItems = findValueInArray($orderItem->item_id, $items, 'item_id');
                                if($itemFoundInItems !== false){
                                    $booked = $timeline[$dateFoundInTimeline]['items'][$itemFoundInItems]['booked'] += $orderItem->quantity;
                                    $timeline[$dateFoundInTimeline]['items'][$itemFoundInItems]['available'] = $totalItems - $booked;
                                } else {
                                    $timeline[$dateFoundInTimeline]['items'][] = $this->buildItem($orderItem, $totalItems);
                                }
                            } else {
                                $timeline[] = [
                                    'date'=>$dayDate,
                                    'items'=>[
                                        $this->buildItem($orderItem, $totalItems)
                                    ]
                                ];
                            }
                        }
                    });
                });
            });

        $timeline = $this->addMissingDays($timeline, $month);
        $stations = Station::pluck('name', 'id');

        return view('timeline', compact('timeline', 'month', 'station', 'stations'));
    }

    private function buildItem(OrderItem $orderItem, int $totalItems): array
    {
        return [
            'item_id'=>$orderItem->item_id,
            'item_name'=>$orderItem->item_name,
            'booked'=>$orderItem->quantity,
            'available'=>$totalItems - $orderItem->quantity
        ];
    }

    private function addMissingDays(array $timeline, int $month): array
    {
        $startDate = now()->month($month)->startOfMonth()->toDateString();
        $diffInDays = now()->month($month)->endOfMonth()->diffInDays($startDate);
        for($i = 0; $i <= $diffInDays; $i++){
            $dayDate = Carbon::parse($startDate)->addDays($i)->toDateString();
            $dateFound = array_search($dayDate, array_column($timeline, 'date'));
            if($dateFound === false){
                $timeline[] = ['date'=>$dayDate, 'items'=>[]];
            }
        }

        usort($timeline, function($firstElement, $secondElement){
            return $firstElement['date'] > $secondElement['date'];
        });

        return $timeline;
    }
}
