<?php


namespace App\Services;


use App\Order;
use App\OrderItem;
use App\Station;
use Carbon\Carbon;

class TimelineService implements TimelineServiceInterface
{

    /**
     * @var Order
     */
    private $order;
    /**
     * @var Station
     */
    private $station;

    public function __construct(Order $order, Station $station)
    {
        $this->order = $order;
        $this->station = $station;
    }

    /**
     * @param int $month
     * @param Station $station
     * @return array
     */
    public function buildTimeline(int $month, Station $station): array
    {
        $timeline = [];
        $this->order
        ->selectOneMonth($month)
        ->wherePickupStation($station)
        ->orderBy('start_date')
        ->chunk(50, function ($orders) use (&$timeline, &$station) {
            $orders->each(function ($order) use (&$station, &$timeline) {
                $bookedDays = Carbon::parse($order->start_date)->diffInDays($order->end_date);
                $order->orderItems->each(function ($orderItem) use (&$bookedDays, &$order, &$station, &$timeline) {
                    $timeline = $this->addItemsToTimeline($bookedDays, $order, $station, $orderItem, $timeline);
                });
            });
        });

        return $this->addMissingDays($timeline, $month);
    }


    /**
     * @param array $timeline
     * @param int $month
     * @return array
     */
    private function addMissingDays(array $timeline, int $month): array
    {
        $startDate = now()->month($month)->startOfMonth()->toDateString();
        $diffInDays = now()->month($month)->endOfMonth()->diffInDays($startDate);
        for ($i = 0; $i <= $diffInDays; $i++) {
            $nextDay = Carbon::parse($startDate)->addDays($i)->toDateString();
            $dateFound = array_search($nextDay, array_column($timeline, 'date'));
            if ($dateFound === false) {
                $timeline[] = ['date' => $nextDay, 'items' => []];
            }
        }

        usort($timeline, function ($firstElement, $secondElement) {
            return $firstElement['date'] > $secondElement['date'];
        });

        return $timeline;
    }

    /**
     * @param int $bookedDays
     * @param Order $order
     * @param Station $station
     * @param OrderItem $orderItem
     * @param array $timeline
     * @return array
     */
    private function addItemsToTimeline(int $bookedDays, Order $order, Station $station, OrderItem $orderItem, array $timeline)
    {
        for ($day = 0; $day < $bookedDays; $day++) {
            $nextDay = Carbon::parse($order->start_date)->addDays($day)->toDateString();
            $totalItems = $station->totalItems($orderItem->item_id, $nextDay);

            if (Carbon::parse($nextDay)->isNextMonth()) {
                continue;
            }

            $dateFoundInTimeline = findValueInArray($nextDay, $timeline, 'date');
            if ($dateFoundInTimeline === false) {
                $timeline[] = [
                    'date' => $nextDay,
                    'items' => [$this->buildItem($orderItem, $totalItems)]
                ];
            }

            if ($dateFoundInTimeline !== false) {
                $items = $timeline[$dateFoundInTimeline]['items'];
                $itemFoundInItems = findValueInArray($orderItem->item_id, $items, 'item_id');

                if ($itemFoundInItems !== false) {
                    $booked = $timeline[$dateFoundInTimeline]['items'][$itemFoundInItems]['booked'] += $orderItem->quantity;
                    $timeline[$dateFoundInTimeline]['items'][$itemFoundInItems]['available'] = $totalItems - $booked;
                } else {
                    $timeline[$dateFoundInTimeline]['items'][] = $this->buildItem($orderItem, $totalItems);
                }
            }
        }

        return $timeline;
    }

    /**
     * @param OrderItem $orderItem
     * @param int $totalItems
     * @return array
     */
    private function buildItem(OrderItem $orderItem, int $totalItems): array
    {
        return [
            'item_id' => $orderItem->item_id,
            'item_name' => $orderItem->item_name,
            'booked' => $orderItem->quantity,
            'available' => $totalItems - $orderItem->quantity
        ];
    }
}
