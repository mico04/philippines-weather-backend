<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PhilippinesCityTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_philippines_cities(): void
    {
        $response = $this->get('/api/philippines/cities');

        $api_cities = Http::get('https://countriesnow.space/api/v0.1/countries/population/cities')->json()['data'];

        $get_all_philippines_cities = array_column(
            array_filter($api_cities, function ($city) {
                return $city['country'] === 'Philippines';
            }),
            'city'
        );

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'status_code' => 200,
            'message' => 'Load successfully.',
            'data' => $get_all_philippines_cities
        ]);
    }
}
