<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Traits\ResponseTrait;

class WeatherCityController extends Controller
{

    use ResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $weather_codes = [
            // Clear & Clouds
            0  => "Clear sky",
            1  => "Mainly clear",
            2  => "Partly cloudy",
            3  => "Overcast",

            // Fog & Smoke
            45 => "Fog",
            48 => "Depositing rime fog",

            // Drizzle
            51 => "Light drizzle",
            53 => "Moderate drizzle",
            55 => "Dense drizzle",
            56 => "Light freezing drizzle",
            57 => "Dense freezing drizzle",

            // Rain
            61 => "Slight rain",
            63 => "Moderate rain",
            65 => "Heavy rain",
            66 => "Light freezing rain",
            67 => "Heavy freezing rain",

            // Snow
            71 => "Slight snowfall",
            73 => "Moderate snowfall",
            75 => "Heavy snowfall",
            77 => "Snow grains",
            85 => "Slight snow showers",
            86 => "Heavy snow showers",

            // Rain Showers
            80 => "Slight rain showers",
            81 => "Moderate rain showers",
            82 => "Violent rain showers",

            // Thunderstorms
            95 => "Slight or moderate thunderstorm",
            96 => "Thunderstorm with slight hail",
            99 => "Thunderstorm with heavy hail",
        ];

        $result = $this->successResponse('Load successfully.');
        try {
            $current_date = date('Y-m-d');

            $city_longitude_latitude = Http::get("https://geocoding-api.open-meteo.com/v1/search?name={$id}&count=1&language=en&format=json")->json()['results'][0];

            $city_weather = Http::get("https://api.open-meteo.com/v1/forecast?latitude={$city_longitude_latitude['latitude']}&longitude={$city_longitude_latitude['longitude']}&current=temperature_2m,precipitation,weather_code,wind_speed_10m&timezone=auto&start_date={$current_date}&end_date={$current_date}")->json();

            $result['data'] = [
                'city' => $id,
                'temperature' => "{$city_weather['current']['temperature_2m']}{$city_weather['current_units']['temperature_2m']}",
                'description' => $weather_codes[$city_weather['current']['weather_code']] ?? 'Unknown',
                'humidity' => "{$city_weather['current']['precipitation']}{$city_weather['current_units']['precipitation']}",
                'wind_speed' => "{$city_weather['current']['wind_speed_10m']}{$city_weather['current_units']['wind_speed_10m']}"
            ];

        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }

        return $result;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
