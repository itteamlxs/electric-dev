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
        
        // Obtener precios del día
        $prices = $this->priceModel->findByDate($date);
        
        if (count($prices) !== 24) {
            Logger::error("Cannot classify: incomplete price data for $date");
            return false;
        }
        
        // Calcular umbrales
        $stats = $this->priceModel->getStatsByDate($date);
        $thresholds = $this->calculateThresholds($stats);
        
        // Clasificar cada hora
        $classifications = [];
        foreach ($prices as $price) {
            $classification = $this->classifyHour($price['price_eur_mwh'], $thresholds);
            
            $classifications[] = [
                'price_date' => $date,
                'hour' => $price['hour'],
                'classification' => $classification
            ];
        }
        
        // Guardar clasificaciones
        try {
            $this->classificationModel->insertBulk($classifications);
            Logger::info("Successfully classified 24 hours for date: $date");
            return true;
        } catch (\Exception $e) {
            Logger::error("Classification save failed: " . $e->getMessage());
            return false;
        }
    }
    
    private function calculateThresholds(array $stats): array
    {
        $min = $stats['min_price'];
        $max = $stats['max_price'];
        $range = $max - $min;
        
        // Umbral bajo: 33% del rango desde el mínimo
        // Umbral alto: 67% del rango desde el mínimo
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
