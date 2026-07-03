<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Cache;

class PhilippinesCityController extends Controller
{

    use ResponseTrait;

    /**
     * Display a listing of the resource.
     */
public function index()
{
    $result = $this->successResponse('Load successfully.');

    try {
        $get_all_philippines_cities = Cache::remember(
            'philippines_cities',
            now()->addDay(), // Cache for 24 hours
            function () {
                $api_cities = Http::get(
                    'https://countriesnow.space/api/v0.1/countries/population/cities'
                )->json()['data'];

                return array_values(array_column(
                    array_filter($api_cities, function ($city) {
                        return $city['country'] === 'Philippines';
                    }),
                    'city'
                ));
            }
        );

        $result['data'] = $get_all_philippines_cities;
    } catch (\Exception $e) {
        return $this->errorResponse($e);
    }

    return $result;
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
        //
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
