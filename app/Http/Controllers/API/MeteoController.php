<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WeatherService;
use Illuminate\Http\Request;

class MeteoController extends Controller
{
    protected $weatherServices;

    public function __construct(WeatherService $weatherServices){
        $this->weatherService = $weatherServices;
    }

    public function index(Request $request){
        $city = $request->get('city');
        $data = $this->weatherService->getWeather($city);
        return response()->json($data);
    }
}