<?php

namespace App\Services;

use App\Models\ElectricityPrice;
use App\Models\HourClassification;
use App\Config\Logger;

class HourClassifier
{
    private ElectricityPrice $priceModel;
    private HourClassification $classificationModel;
    
    public function __construct()
    {
        $this->priceModel = new ElectricityPrice();
        $this->classificationModel = new HourClassification();
    }
    
    public function classifyDate(string $date): bool
    {
        Logger::info("Starting classification for date: $date");
        
        $zones = $this->priceModel->getAvailableZones($date);
        
        if (empty($zones)) {
            Logger::error("No zones found for date: $date");
            return false;
        }
        
        foreach ($zones as $zone) {
            if (!$this->classifyZone($date, $zone['geo_id'])) {
                Logger::error("Failed to classify zone {$zone['geo_name']}");
                return false;
            }
        }
        
        Logger::info("Successfully classified all zones for date: $date");
        return true;
    }
    
    private function classifyZone(string $date, int $geoId): bool
    {
        $prices = $this->priceModel->findByDate($date, $geoId);
        
        if (count($prices) !== 24) {
            Logger::error("Cannot classify zone $geoId: incomplete price data for $date");
            return false;
        }
        
        $stats = $this->priceModel->getStatsByDate($date, $geoId);
        $thresholds = $this->calculateThresholds($stats);
        
        $classifications = [];
        foreach ($prices as $price) {
            $classification = $this->classifyHour($price['price_eur_mwh'], $thresholds);
            
            $classifications[] = [
                'price_date' => $date,
                'hour' => $price['hour'],
                'geo_id' => $geoId,
                'classification' => $classification
            ];
        }
        
        try {
            $this->classificationModel->insertBulk($classifications);
            return true;
        } catch (\Exception $e) {
            Logger::error("Classification save failed for zone $geoId: " . $e->getMessage());
            return false;
        }
    }
    
    private function calculateThresholds(array $stats): array
    {
        $min = $stats['min_price'];
        $max = $stats['max_price'];
        $range = $max - $min;
        
        return [
            'low' => $min + ($range * 0.33),
            'high' => $min + ($range * 0.67)
        ];
    }
    
    private function classifyHour(float $price, array $thresholds): string
    {
        if ($price <= $thresholds['low']) {
            return 'buena';
        } elseif ($price <= $thresholds['high']) {
            return 'normal';
        } else {
            return 'cara';
        }
    }
}
