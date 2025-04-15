<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    private const CACHE_KEY = 'exchange_rate_usd_eur';
    private const CACHE_TTL = 3600; // 1 hour

    public function getUsdToEurRate(): float
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            try {
                $response = Http::timeout(5)->get('https://open.er-api.com/v6/latest/USD');
                
                if ($response->successful() && isset($response['rates']['EUR'])) {
                    return $response['rates']['EUR'];
                }
                
                throw new \Exception('Failed to fetch exchange rate');
            } catch (\Exception $e) {
                Log::error('Exchange rate fetch failed: ' . $e->getMessage());
                return config('app.default_exchange_rate', 0.85);
            }
        });
    }
}
