@extends('layouts.app')
<?php
    use Carbon\Carbon as CarbonAlias;
?>

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12 timeline-pagination">
                <h3 class="today-title"><b>{{isset($month) ? CarbonAlias::parse($timeline[0]['date'])->monthName : now()->monthName}}</b>
                    {{' '.CarbonAlias::parse($timeline[0]['date'])->year}}
                </h3>
                <div class="carousel-control-wrapper">
                    <a class="carousel-control-next carousel-control-next-icon"
                       data-toggle="tooltip" data-placement="top" title="Next Month"
                       href="{{isset($month) ? request()->fullUrlWithQuery(['month'=>$month + 1]) : request()->fullUrlWithQuery(['month', now()->month + 1]) }}"></a>
                    <a class="carousel-control-prev carousel-control-prev-icon"
                       data-toggle="tooltip" data-placement="top" title="Prev Month"
                       href="{{isset($month) ? request()->fullUrlWithQuery(['month'=>$month - 1]) : request()->fullUrlWithQuery(['month'=>now()->month]) }}"></a>
                </div>
                <div class="station_wrapper">
                    <select class="form-select station-select" aria-label="Default select example">
                        @foreach($stations as $stationId => $stationName)
                            @if($station->id == $stationId)
                                <option selected value="{{$stationId}}">{{$stationName}}</option>
                            @else
                                <option value="{{$stationId}}">{{$stationName}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-12 remove-padding timeline-border">
                <div class="day-header text-right">Sun</div>
                <div class="day-header text-right">Mon</div>
                <div class="day-header text-right">Tue</div>
                <div class="day-header text-right">Wed</div>
                <div class="day-header text-right">Thu</div>
                <div class="day-header text-right">Fri</div>
                <div class="day-header text-right">Sat</div>
            </div>

            <div class="col-md-12 col-sm-12 col-lg-12 remove-padding border">

                {{-- Print prev month days--}}
                @for($i = CarbonAlias::parse($timeline[0]['date'])->dayOfWeek; $i > 0 ; $i--)
                    <div class="day disabled prev-month">
                        <span class="day-in-month">{{CarbonAlias::parse($timeline[0]['date'])->subDays($i)->day}}</span>
                    </div>
                @endfor

                @foreach($timeline as $day)
                    <div class="day {{CarbonAlias::parse($day['date'])->isWeekend() ? 'weekend' : ''}}">
                        <div
                            class="day-in-month
                            {{now()->toDateString() == CarbonAlias::parse($day['date'])->toDateString() ? "badge badge-pill badge-danger": ""}}">
                            {{CarbonAlias::parse($day['date'])->day}}
                        </div>
                        @foreach($day['items'] as $item)
                            <div class="item">
                                <span>{{$item['item_name']}}</span>
                                <span data-toggle="tooltip"
                                      data-placement="top"
                                      title="Booked/Remaining"
                                      class="badge badge-pill badge-info booked-badge">
                                    {{$item['booked'] . ' / '. $item['available']}}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endforeach

                {{-- Print next month days--}}
                @for($i = 1; $i <= 6 - CarbonAlias::parse($timeline[count($timeline) - 1]['date'])->dayOfWeek ; $i++)
                    <div class="day disabled next-month {{CarbonAlias::parse($day['date'])->addDays($i)->isWeekend() ? 'weekend' : ''}}">
                        <span class="day-in-month">{{CarbonAlias::parse($timeline[count($timeline) - 1]['date'])->addDays($i)->day}}</span>
                    </div>
                @endfor
            </div>
        </div>
    </div>
    </div>
@endsection
