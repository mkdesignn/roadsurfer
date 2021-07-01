<?php

namespace App\Http\Controllers;

use App\Order;
use Illuminate\Http\Request;

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
     * TimelineController constructor.
     * @param Order $order
     * @param Request $request
     */
    public function __construct(Order $order, Request $request)
    {
        $this->order = $order;
        $this->request = $request;
    }

    public function index()
    {
        $month = $this->request->has('month') ? $this->request->month : 1;
//        $orders = $this->order
//            ->where('start_date', '>=', now()->addMonths($month - 1)->toDateTimeString())
//            ->where('end_date', '<=', now()->addMonths($month)->toDateTimeString())->get();

        $timeline = [];
        for($i = 0; $i <= 29; $i++){
            $timeline[$i] = [];
        }

        return view('timeline', compact('timeline'));
    }
}
