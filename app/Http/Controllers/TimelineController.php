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
     * @var Request
     */
    private $request;
    /**
     * @var Station
     */
    private $station;

    /**
     * TimelineController constructor.
     * @param Order $order
     * @param Request $request
     * @param Station $station
     */
    public function __construct(Order $order, Request $request, Station $station)
    {
        $this->order = $order;
        $this->request = $request;
        $this->station = $station;
    }

    public function index()
    {
        $month = $this->request->has('month') ? $this->request->month : 0;
        $station = $this->request->has('station') ?
            $this->station->where('name', $this->request->station)->first() :
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

                        $totalItems = $station->items()->where('items.id', $orderItem->item_id)->first()->pivot->amount;
                        for ($day = 0; $day < $bookedDays; $day++) {
                            $dayDate = Carbon::parse($order->start_date)->addDays($day)->toDateString();

                            if(Carbon::parse($dayDate)->isNextMonth()){
                                continue;
                            }

                            $dateFoundInTimeline = array_search($dayDate, array_column($timeline, 'date'));
                            if($dateFoundInTimeline !== false){
                                $items = $timeline[$dateFoundInTimeline]['items'];
                                $itemFoundInItems = array_search($orderItem->item_id, array_column($items, 'item_id'));
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

        return view('timeline', compact('timeline'));
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
        $startDate = now()->addMonths($month)->startOfMonth()->toDateString();
        $diffInDays = now()->addMonths($month)->endOfMonth()->diffInDays($startDate);
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
