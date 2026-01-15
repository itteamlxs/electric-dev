<?php

namespace App\Services;

class MockPriceProvider
{
    public static function generateMockPrices(string $date): array
    {
        $prices = [];
        $basePrice = 120.0;
        
        for ($hour = 0; $hour < 24; $hour++) {
            // Simular patrón real: más caro 18-22h, más barato 2-6h
            if ($hour >= 2 && $hour <= 6) {
                $price = $basePrice * 0.6 + rand(-10, 10);
            } elseif ($hour >= 18 && $hour <= 22) {
                $price = $basePrice * 1.4 + rand(-10, 10);
            } else {
                $price = $basePrice + rand(-20, 20);
            }
            
            $prices[] = [
                'price_date' => $date,
                'hour' => $hour,
                'price_eur_mwh' => round($price, 5)
            ];
        }
        
        return $prices;
    }
}
