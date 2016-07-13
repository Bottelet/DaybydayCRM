<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class AnalyticsController extends Controller
{
    public function ga()
    {
    	return view('analytics.ga');
    }
}
