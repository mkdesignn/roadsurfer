<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Requests\TimelineRequest;
use App\Http\Resources\TimelineResource;
use App\Services\TimelineServiceInterface;
use App\Station;
use Illuminate\Http\Request;

class TimelineController extends Controller
{
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
     * @param Station $station
     * @param TimelineServiceInterface $timelineService
     */
    public function __construct(Station $station, TimelineServiceInterface $timelineService)
    {
        $this->station = $station;
        $this->timelineService = $timelineService;
    }

    /**
     * @OA\Get(
     *     path="/api/timeline",
     *     @OA\Parameter(
     *          name="month",
     *          in="query",
     *          required=false
     *      ),
     *     @OA\Parameter(
     *          name="station",
     *          in="query",
     *          required=false
     *      ),
     *     description="Home page",
     *     @OA\Response(response="default", description="Return the timeline for one month")
     * )
     */
    public function index(TimelineRequest $request)
    {
        $month = $request->has('month') ? $request->month : now()->month;
        $station = $request->has('station') ?
            $this->station->where('id', $request->station)->first() :
            $this->station->default()->first();

        return TimelineResource::make($this->timelineService->buildTimeline($month, $station));
    }
}
