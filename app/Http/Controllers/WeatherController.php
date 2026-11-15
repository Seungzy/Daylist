<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    public function show(Request $request)
    {
        abort_unless($request->user() || $request->hasValidSignatureWhileIgnoring(['lat', 'lon']), 403);
        $data = $request->validate(['lat' => 'required|numeric|between:-90,90', 'lon' => 'required|numeric|between:-180,180']);
        if (! config('services.openweather.key')) {
            return response()->json(['message' => 'Weather is not configured yet. Add OPENWEATHER_KEY to the server settings.'], 503);
        }
        $lat = round((float) $data['lat'], 4);
        $lon = round((float) $data['lon'], 4);
        $cacheKey = 'weather:'.$lat.':'.$lon;
        if ($cached = Cache::get($cacheKey)) {
            return response()->json($cached);
        }
        try {
            $response = Http::timeout(8)->connectTimeout(4)->get('https://api.openweathermap.org/data/2.5/weather', ['lat' => $lat, 'lon' => $lon, 'units' => 'metric', 'appid' => config('services.openweather.key')]);
            if (! $response->successful() || ! is_numeric($response->json('main.temp'))) {
                return response()->json(['message' => 'Weather is currently unavailable. Please try again later.'], 502);
            }
            $weather = ['place' => $response->json('name', 'Selected location'), 'temperature' => $response->json('main.temp'), 'description' => $response->json('weather.0.description'), 'humidity' => $response->json('main.humidity')];
            Cache::put($cacheKey, $weather, now()->addMinutes(5));

            return response()->json($weather);
        } catch (ConnectionException $e) {
            return response()->json(['message' => 'Weather is currently unavailable. Please try again later.'], 502);
        }
    }
}
