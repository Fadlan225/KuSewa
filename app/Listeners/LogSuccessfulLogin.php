<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\AccountActivity;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Http;
use App\Models\Province;
use App\Models\city; // model name is lowercase city based on previous check
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        try {
            $request = request();
            $agent = new Agent();
            
            $ip = $request->ip();
            // For local testing, use a public IP or handle 127.0.0.1
            if ($ip === '127.0.0.1' || $ip === '::1') {
                $ip = '103.111.143.15'; // Dummy indonesian IP for local dev
            }

            $provinceCode = null;
            $regencyCode = null;

            // Simple IP Geolocation (Note: free ip-api limit is 45 requests per minute)
            try {
                $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}");
                if ($response->successful() && $response->json('status') === 'success') {
                    $regionName = $response->json('regionName'); // e.g. "East Java" or "Jawa Timur"
                    $cityName = $response->json('city'); // e.g. "Surabaya"

                    // Try to match Province
                    if ($regionName) {
                        // Very naive matching, might not match english names perfectly
                        $province = Province::where('name', 'like', '%' . $regionName . '%')->first();
                        if ($province) {
                            $provinceCode = $province->code;
                        }
                    }

                    // Try to match City
                    if ($cityName) {
                        $city = city::where('name', 'like', '%' . $cityName . '%')->first();
                        if ($city) {
                            $regencyCode = $city->code;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Ignore timeout or API error so it doesn't block login
                Log::error('IP Geolocation failed: ' . $e->getMessage());
            }

            $deviceInfo = \App\Services\DeviceDetectorService::detect($request->header('User-Agent'));

            AccountActivity::create([
                'user_id' => $event->user->id,
                'type' => 'login',
                'ip_address' => $request->ip(),
                'user_agent' => $deviceInfo['raw_agent'],
                'os' => $deviceInfo['os'],
                'device_name' => $deviceInfo['device_name'],
                'device_type' => $deviceInfo['device_type'],
                'browser' => $deviceInfo['browser'],
                'province_code' => $provinceCode,
                'regency_code' => $regencyCode,
            ]);
        } catch (\Exception $e) {
            // Log error but don't prevent user from logging in
            Log::error('LogSuccessfulLogin failed: ' . $e->getMessage());
        }
    }
}
