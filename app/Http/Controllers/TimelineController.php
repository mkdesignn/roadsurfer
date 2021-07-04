<?php

namespace App\Http\Controllers;

use App\Http\Resources\TimelineResource;
use App\Order;
use App\OrderItem;
use App\Services\TimelineServiceInterface;
use App\Station;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
     * @var TimelineServiceInterface
     */
    private $timelineService;

    /**
     * TimelineController constructor.
     * @param Order $order
     * @param Station $station
     * @param TimelineServiceInterface $timelineService
     */
    public function __construct(Order $order, Station $station, TimelineServiceInterface $timelineService)
    {
        $this->order = $order;
        $this->station = $station;
        $this->timelineService = $timelineService;
    }

    /**
     * @param Request $request
     */
    public function index(Request $request)
    {
        $month = $request->has('month') ? $request->month : now()->month;
        $station = $request->has('station') ?
            $this->station->where('id', $request->station)->first() :
            $this->station->default()->first();

        $timeline = $this->timelineService->buildTimeline($month, $station);

        $stations = Station::pluck('name', 'id');

        if($request->ajax()){
            return TimelineResource::make($timeline);
        }

        return view('timeline', compact('timeline', 'month', 'station', 'stations'));
    }
}
