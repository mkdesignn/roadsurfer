<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/timeline', 'Api\TimelineController@index');
