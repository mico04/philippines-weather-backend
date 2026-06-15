<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherCityTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_weather_city(): void
    {
        $weatherCodes = [
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
            0  => '01d', // Clear Sky
            1  => '02d', // Few Clouds
            2  => '03d', // Scattered Clouds
            3  => '04d', // Broken Clouds

            45 => '50d', // Mist/Fog
            48 => '50d',

            51 => '09d', // Drizzle
            53 => '09d',
            55 => '09d',
            56 => '09d',
            57 => '09d',

            61 => '10d', // Rain
            63 => '10d',
            65 => '10d',
            66 => '10d',
            67 => '10d',

            71 => '13d', // Snow
            73 => '13d',
            75 => '13d',
            77 => '13d',
            85 => '13d',
            86 => '13d',

            80 => '09d', // Shower Rain
            81 => '09d',
            82 => '09d',

            95 => '11d', // Thunderstorm
            96 => '11d',
            99 => '11d',
        ];


        $city = 'Manila';
        $current_date = date('Y-m-d');
        $response = $this->get("/api/weather/city/{$city}");

        $city_longitude_latitude = Http::get("https://geocoding-api.open-meteo.com/v1/search?name={$city}&count=1&language=en&format=json")->json()['results'][0];

        $city_weather = Http::get("https://api.open-meteo.com/v1/forecast?latitude={$city_longitude_latitude['latitude']}&longitude={$city_longitude_latitude['longitude']}&current=temperature_2m,precipitation,weather_code,wind_speed_10m,relative_humidity_2m,cloud_cover&timezone=auto&start_date={$current_date}&end_date={$current_date}")->json();

        $feels_like = $this->heatIndex($city_weather['current']['temperature_2m'], $city_weather['current']['relative_humidity_2m']);

        $response->assertStatus(200);

        $response->assertJson([
            'status' => 'success',
            'status_code' => 200,
            'message' => 'Load successfully.',
            'data' => [
                'city' => $city,
                'temperature' => "{$city_weather['current']['temperature_2m']}{$city_weather['current_units']['temperature_2m']}",
                'description' => $weatherCodes[$city_weather['current']['weather_code']] ?? 'Unknown',
                'humidity' => "{$city_weather['current']['relative_humidity_2m']}{$city_weather['current_units']['relative_humidity_2m']}",
                'wind_speed' => "{$city_weather['current']['wind_speed_10m']}{$city_weather['current_units']['wind_speed_10m']}",
                'cloud_cover' => "{$city_weather['current']['cloud_cover']}{$city_weather['current_units']['cloud_cover']}",
                'feels_like' => "{$feels_like}°C",
                'icon' => $weatherCodeToIcon[$city_weather['current']['weather_code']] ?? 'unknown'
            ]
        ]);
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
}
