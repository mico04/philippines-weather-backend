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

        $weatherCodeToIcon = [
            0  => '01', // Clear Sky
            1  => '02', // Few Clouds
            2  => '03', // Scattered Clouds
            3  => '04', // Broken Clouds

            45 => '50', // Mist/Fog
            48 => '50',

            51 => '09', // Drizzle
            53 => '09',
            55 => '09',
            56 => '09',
            57 => '09',

            61 => '10', // Rain
            63 => '10',
            65 => '10',
            66 => '10',
            67 => '10',

            71 => '13', // Snow
            73 => '13',
            75 => '13',
            77 => '13',
            85 => '13',
            86 => '13',

            80 => '09', // Shower Rain
            81 => '09',
            82 => '09',

            95 => '11', // Thunderstorm
            96 => '11',
            99 => '11',
        ];

        $result = $this->successResponse('Load successfully.');
        try {
            $current_date = date('Y-m-d');

            $city_longitude_latitude = Http::timeout(500)
    ->connectTimeout(30)->get("https://geocoding-api.open-meteo.com/v1/search?name={$id}&count=1&language=en&format=json")->json()['results'][0];

            $city_weather = Http::timeout(500)
    ->connectTimeout(30)->get("https://api.open-meteo.com/v1/forecast?latitude={$city_longitude_latitude['latitude']}&longitude={$city_longitude_latitude['longitude']}&current=temperature_2m,precipitation,weather_code,wind_speed_10m,relative_humidity_2m,cloud_cover&timezone=auto&start_date={$current_date}&end_date={$current_date}")->json();

            $feels_like = $this->heatIndex($city_weather['current']['temperature_2m'], $city_weather['current']['relative_humidity_2m']);

            $result['data'] = [
                'city' => "{$id}, {$city_longitude_latitude['admin2']}",
                'temperature' => $city_weather['current']['temperature_2m'],
                'description' => $weather_codes[$city_weather['current']['weather_code']] ?? 'Unknown',
                'humidity' => $city_weather['current']['relative_humidity_2m'],
                'wind_speed' => "{$city_weather['current']['wind_speed_10m']}{$city_weather['current_units']['wind_speed_10m']}",
                'cloud_cover' => $city_weather['current']['cloud_cover'],
                'feels_like' => $feels_like,
                'icon' => $weatherCodeToIcon[$city_weather['current']['weather_code']] ?? 'unknown'
            ];

        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }

        return $result;
    }

    public function heatIndex(int $tempC, int $humidity)
    {
        // Convert Celsius to Fahrenheit
        $tempF = ($tempC * 9 / 5) + 32;

        // NOAA heat index formula
        $hiF = -42.379
            + 2.04901523 * $tempF
            + 10.14333127 * $humidity
            - 0.22475541 * $tempF * $humidity
            - 0.00683783 * $tempF * $tempF
            - 0.05481717 * $humidity * $humidity
            + 0.00122874 * $tempF * $tempF * $humidity
            + 0.00085282 * $tempF * $humidity * $humidity
            - 0.00000199 * $tempF * $tempF * $humidity * $humidity;

        // Convert back to Celsius
        $hiC = ($hiF - 32) * 5 / 9;

        return round($hiC, 1);
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
