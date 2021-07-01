<?php

namespace Tests\Feature;

use App\Http\Controllers\TimelineController;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class timelineTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @see TimelineController::index()
     */
    public function testPageExists()
    {
        $this->get('/timeline')->assertStatus(200);
    }

    /**
     * @see TimelineController::index()
     */
    public function testPageReturnsTimeLineObject()
    {
        $this->get('/timeline')->assertViewHas('timeline');
    }

    /**
     * @see TimelineController::index()
     */
    public function testPageReturnsTimeLineObjectWhichContains30Elements()
    {
        $timeline = [];
        for($i = 0; $i <= 29; $i++){
            $timeline[] = [];
        }

        $this->get('/timeline')
            ->assertViewHas('timeline', $timeline);
    }
}
