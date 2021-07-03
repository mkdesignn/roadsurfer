@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12 timeline-pagination">
                <h3 class="today-title"><b>{{now()->monthName}}</b>{{' '.now()->year}}</h3>
            </div>

            <div class="col-md-12 remove-padding timeline-border">
                <div class="day-header text-right">Mon</div>
                <div class="day-header text-right">Tue</div>
                <div class="day-header text-right">Wed</div>
                <div class="day-header text-right">Thu</div>
                <div class="day-header text-right">Fri</div>
                <div class="day-header text-right">Sat</div>
                <div class="day-header text-right">Sun</div>
            </div>

            <div class="col-md-12 col-sm-12 col-lg-12 remove-padding border">
                @for($i = \Carbon\Carbon::parse($timeline[0]['date'])->dayOfWeek - 1; $i > 0 ; $i--)
                    <div class="day disabled prev-month">
                        <span class="day-in-month">{{\Carbon\Carbon::parse($timeline[0]['date'])->subDays($i)->day}}</span>
                    </div>
                @endfor

                @foreach($timeline as $day)
                    <div class="day {{\Carbon\Carbon::parse($day['date'])->isWeekend() ? 'weekend' : ''}}">
                        <div class="day-in-month {{now()->day == \Carbon\Carbon::parse($day['date'])->day ? "badge badge-pill badge-danger": ""}}">
                            {{\Carbon\Carbon::parse($day['date'])->day}}
                        </div>
                        @foreach($day['items'] as $item)
                            <div class="item">
                                <span>{{$item['item_name']}}</span>
                                <span class="badge badge-pill badge-info booked-badge">
                                    {{$item['booked'] . ' / '. $item['available']}}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endforeach

                @for($i = 1; $i <= 7 - \Carbon\Carbon::parse($timeline[count($timeline) - 1]['date'])->dayOfWeek ; $i++)
                    <div class="day disabled next-month {{\Carbon\Carbon::parse($day['date'])->isWeekend() ? 'weekend' : ''}}">
                        <span class="day-in-month">{{\Carbon\Carbon::parse($timeline[count($timeline) - 1]['date'])->addDays($i)->day}}</span>
                    </div>
                @endfor
            </div>
        </div>
    </div>
    </div>
@endsection
