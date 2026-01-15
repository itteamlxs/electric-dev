<?php

namespace App\Services;

class MockPriceProvider
{
    private const ZONES = [
        8741 => 'Península',
        8742 => 'Canarias',
        8743 => 'Baleares',
        8744 => 'Ceuta',
        8745 => 'Melilla'
    ];
    
    public static function generateMockPrices(string $date): array
    {
        $prices = [];
        $basePrice = 120.0;
        
        foreach (self::ZONES as $geoId => $geoName) {
            $zoneFactor = ($geoId === 8741) ? 1.0 : 1.1;
            
            for ($hour = 0; $hour < 24; $hour++) {
                if ($hour >= 2 && $hour <= 6) {
                    $price = $basePrice * 0.6 * $zoneFactor + rand(-10, 10);
                } elseif ($hour >= 18 && $hour <= 22) {
                    $price = $basePrice * 1.4 * $zoneFactor + rand(-10, 10);
                } else {
                    $price = $basePrice * $zoneFactor + rand(-20, 20);
                }
                
                $prices[] = [
                    'price_date' => $date,
                    'hour' => $hour,
                    'geo_id' => $geoId,
                    'geo_name' => $geoName,
                    'price_eur_mwh' => round($price, 5)
                ];
            }
        }
        
        return $prices;
    }
}
