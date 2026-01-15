<?php

namespace App\Services;

use App\Models\ElectricityPrice;
use App\Config\Logger;

class PriceImporter
{
    private ElectricityApiClient $apiClient;
    private ElectricityPrice $priceModel;
    private bool $useMock;
    
    public function __construct()
    {
        $this->apiClient = new ElectricityApiClient();
        $this->priceModel = new ElectricityPrice();
        $this->useMock = empty($_ENV['ESIOS_API_TOKEN']);
    }
    
    public function importForDate(string $date): bool
    {
        Logger::info("Starting price import for date: $date");
        
        if (!$this->isValidDate($date)) {
            Logger::error("Invalid date format: $date");
            return false;
        }
        
        if ($this->useMock) {
            Logger::info("Using mock data (no API token configured)");
            $prices = MockPriceProvider::generateMockPrices($date);
        } else {
            $prices = $this->apiClient->fetchPricesByDate($date);
            
            if ($prices === null) {
                Logger::warning("API failed, falling back to mock data");
                $prices = MockPriceProvider::generateMockPrices($date);
            }
        }
        
        if (count($prices) !== 24) {
            Logger::warning("Incomplete data: expected 24 hours, got " . count($prices));
            return false;
        }
        
        $prices = $this->sanitizePrices($prices);
        
        try {
            $this->priceModel->insertBulk($prices);
            Logger::info("Successfully imported " . count($prices) . " prices for date: $date");
            return true;
        } catch (\Exception $e) {
            Logger::error("Database insert failed: " . $e->getMessage());
            return false;
        }
    }
    
    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    private function sanitizePrices(array $prices): array
    {
        return array_map(function($price) {
            return [
                'price_date' => $price['price_date'],
                'hour' => (int) $price['hour'],
                'price_eur_mwh' => (float) $price['price_eur_mwh']
            ];
        }, $prices);
    }
}
