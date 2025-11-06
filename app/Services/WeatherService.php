<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WeatherService{
    public function getWeather($city = null){
        $city = $city ?? config('services.openweather.city');
        $apiKey = config('services.openweather.key');

        $response = Http::get('https://api.openweathermap.org/data/2.5/weather', [
            'q' => $city,
            'appid' => $apiKey,
            'units' => 'metric',
            'lang' => 'pt',
        ]);

        if ($response->failed()){
            return [
                'erro' => 'Não foi possível obter os dados meteorologicos',
                'status' => $response->status(),
            ];
        }

        $data = $response->json();

        return [
            'cidade' => $data['name'] ?? $city,
            'temperatura' => round($data['main']['temp'], 1),
            'descricao' => $data ['weather'][0]['description'] ?? '',
            'icone' => $data['weather'][0]['icon'] ?? '',
        ];
    }
}