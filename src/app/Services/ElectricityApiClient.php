<?php

namespace App\Services;

use App\Config\Logger;

class ElectricityApiClient
{
    private const API_URL = 'https://api.esios.ree.es/indicators/1001';
    private const GEO_ID_PENINSULA = 8741;
    private string $apiToken;
    
    public function __construct()
    {
        $this->apiToken = $_ENV['ESIOS_API_TOKEN'] ?? '';
    }
    
    public function fetchPricesByDate(string $date): ?array
    {
        $startDate = $date . 'T00:00:00';
        $endDate = $date . 'T23:59:59';
        
        $url = self::API_URL . '?' . http_build_query([
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'x-api-key: ' . $this->apiToken
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            Logger::error("API request failed: $error");
            return null;
        }
        
        if ($httpCode !== 200) {
            Logger::warning("API returned code $httpCode for date $date");
            return null;
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['indicator']['values'])) {
            Logger::error("Invalid API response structure");
            return null;
        }
        
        return $this->parseApiResponse($data['indicator']['values'], $date);
    }
    
    private function parseApiResponse(array $values, string $date): array
    {
        $prices = [];
        
        foreach ($values as $item) {
            // Filtrar solo Península
            if ($item['geo_id'] != self::GEO_ID_PENINSULA) {
                continue;
            }
            
            $datetime = new \DateTime($item['datetime']);
            
            // Filtrar solo el día solicitado
            if ($datetime->format('Y-m-d') !== $date) {
                continue;
            }
            
            $hour = (int) $datetime->format('H');
            $price = (float) $item['value'] / 1000;
            
            $prices[] = [
                'price_date' => $date,
                'hour' => $hour,
                'price_eur_mwh' => $price
            ];
        }
        
        return $prices;
    }
}
