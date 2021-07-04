<?php


namespace App\Services;


use App\Station;

interface TimelineServiceInterface
{

    public function buildTimeline(int $month, Station $station): array;
}
