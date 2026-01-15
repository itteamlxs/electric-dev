<?php

namespace App\Controllers;

use App\Models\ElectricityPrice;
use App\Models\HourClassification;
use App\Models\Recommendation;
use App\Models\Task;

class ApiController extends BaseController
{
    private ElectricityPrice $priceModel;
    private HourClassification $classificationModel;
    private Recommendation $recommendationModel;
    private Task $taskModel;
    
    public function __construct()
    {
        $this->priceModel = new ElectricityPrice();
        $this->classificationModel = new HourClassification();
        $this->recommendationModel = new Recommendation();
        $this->taskModel = new Task();
    }
    
    public function getToday(): void
    {
        $date = date('Y-m-d');
        $geoId = (int) $this->getQueryParam('geo_id', 8741);
        $this->getSummary($date, $geoId);
    }
    
    public function getTomorrow(): void
    {
        $date = date('Y-m-d', strtotime('+1 day'));
        $geoId = (int) $this->getQueryParam('geo_id', 8741);
        $this->getSummary($date, $geoId);
    }
    
    public function getZones(): void
    {
        $date = $this->getQueryParam('date', date('Y-m-d'));
        
        if (!$this->validateDate($date)) {
            $this->jsonError('Invalid date format', 400);
        }
        
        $zones = $this->priceModel->getAvailableZones($date);
        
        $this->jsonResponse([
            'date' => $date,
            'zones' => $zones
        ]);
    }
    
    public function getHours(): void
    {
        $date = $this->getQueryParam('date', date('Y-m-d'));
        $geoId = (int) $this->getQueryParam('geo_id', 8741);
        
        if (!$this->validateDate($date)) {
            $this->jsonError('Invalid date format. Use Y-m-d', 400);
        }
        
        $prices = $this->priceModel->findByDate($date, $geoId);
        $classifications = $this->classificationModel->findByDate($date, $geoId);
        
        if (empty($prices)) {
            $this->jsonError('No data available for this date', 404);
        }
        
        $hours = [];
        foreach ($prices as $price) {
            $classification = array_values(array_filter($classifications, fn($c) => $c['hour'] == $price['hour']));
            
            $hours[] = [
                'hour' => $price['hour'],
                'price' => round($price['price_eur_mwh'], 3),
                'classification' => $classification[0]['classification'] ?? 'normal',
                'label' => $this->getLabel($classification[0]['classification'] ?? 'normal')
            ];
        }
        
        $this->jsonResponse([
            'date' => $date,
            'geo_id' => $geoId,
            'geo_name' => $prices[0]['geo_name'] ?? 'Desconocida',
            'hours' => $hours
        ]);
    }
    
    public function getTaskRecommendation(string $taskCode): void
    {
        $date = $this->getQueryParam('date', date('Y-m-d'));
        $geoId = (int) $this->getQueryParam('geo_id', 8741);
        
        if (!$this->validateDate($date)) {
            $this->jsonError('Invalid date format. Use Y-m-d', 400);
        }
        
        $task = $this->taskModel->findByCode($taskCode);
        
        if (!$task) {
            $this->jsonError('Task not found', 404);
        }
        
        $recommendation = $this->recommendationModel->findByDateAndTask($date, $task['id'], $geoId);
        
        if (!$recommendation) {
            $this->jsonError('No recommendation available for this date', 404);
        }
        
        $this->jsonResponse([
            'date' => $date,
            'geo_id' => $geoId,
            'task' => $task['task_name'],
            'recommended_hours' => $recommendation['recommended_hours'],
            'message' => $this->formatRecommendationMessage($recommendation['recommended_hours'], $task['task_name'])
        ]);
    }
    
    private function getSummary(string $date, int $geoId): void
    {
        if (!$this->validateDate($date)) {
            $this->jsonError('Invalid date format', 400);
        }
        
        $recommendations = $this->recommendationModel->findByDate($date, $geoId);
        $stats = $this->priceModel->getStatsByDate($date, $geoId);
        
        if (empty($recommendations)) {
            $this->jsonError('No data available for this date', 404);
        }
        
        $summary = [
            'date' => $date,
            'geo_id' => $geoId,
            'geo_name' => $recommendations[0]['geo_name'] ?? 'Desconocida',
            'price_range' => [
                'min' => round($stats['min_price'], 3),
                'max' => round($stats['max_price'], 3),
                'avg' => round($stats['avg_price'], 3)
            ],
            'recommendations' => array_map(fn($r) => [
                'task' => $r['task_name'],
                'task_code' => $r['task_code'],
                'recommended_hours' => $r['recommended_hours'],
                'message' => $this->formatRecommendationMessage($r['recommended_hours'], $r['task_name'])
            ], $recommendations)
        ];
        
        $this->jsonResponse($summary);
    }
    
    private function formatRecommendationMessage(array $hours, string $taskName): string
    {
        if (empty($hours)) {
            return "No hay horas recomendadas para $taskName hoy";
        }
        
        $first = min($hours);
        $last = max($hours);
        
        return sprintf(
            "Mejor hora para %s: %02d:00 - %02d:00",
            $taskName,
            $first,
            $last + 1
        );
    }
    
    private function getLabel(string $classification): string
    {
        return match($classification) {
            'buena' => 'Ideal para tareas del hogar',
            'normal' => 'Aceptable',
            'cara' => 'Evitar consumo alto',
            default => 'Desconocido'
        };
    }
}
