<?php

namespace App\Services;

use App\Models\Task;
use App\Models\HourClassification;
use App\Models\Recommendation;
use App\Models\ElectricityPrice;
use App\Config\Logger;

class RecommendationGenerator
{
    private Task $taskModel;
    private HourClassification $classificationModel;
    private Recommendation $recommendationModel;
    private ElectricityPrice $priceModel;
    
    public function __construct()
    {
        $this->taskModel = new Task();
        $this->classificationModel = new HourClassification();
        $this->recommendationModel = new Recommendation();
        $this->priceModel = new ElectricityPrice();
    }
    
    public function generateForDate(string $date): bool
    {
        Logger::info("Generating recommendations for date: $date");
        
        $zones = $this->priceModel->getAvailableZones($date);
        
        if (empty($zones)) {
            Logger::error("No zones found for date: $date");
            return false;
        }
        
        foreach ($zones as $zone) {
            if (!$this->generateForZone($date, $zone['geo_id'])) {
                Logger::error("Failed to generate recommendations for zone {$zone['geo_name']}");
                return false;
            }
        }
        
        Logger::info("Successfully generated recommendations for all zones");
        return true;
    }
    
    private function generateForZone(string $date, int $geoId): bool
    {
        $tasks = $this->taskModel->getAllActive();
        
        if (empty($tasks)) {
            Logger::error("No tasks found");
            return false;
        }
        
        $goodHours = $this->classificationModel->getByDateAndClassification($date, 'buena', $geoId);
        
        if (empty($goodHours)) {
            Logger::warning("No good hours found for zone $geoId on $date, using normal hours");
            $goodHours = $this->classificationModel->getByDateAndClassification($date, 'normal', $geoId);
        }
        
        foreach ($tasks as $task) {
            $recommendedHours = $this->selectHoursForTask($task, $goodHours);
            
            try {
                $this->recommendationModel->insertOrUpdate(
                    $task['id'],
                    $date,
                    $recommendedHours,
                    $geoId
                );
            } catch (\Exception $e) {
                Logger::error("Failed to save recommendation for task {$task['task_code']} zone $geoId: " . $e->getMessage());
                return false;
            }
        }
        
        return true;
    }
    
    private function selectHoursForTask(array $task, array $availableHours): array
    {
        $minDuration = $task['min_duration_hours'];
        $recommended = [];
        
        $blocks = $this->findConsecutiveBlocks($availableHours, $minDuration);
        
        if (!empty($blocks)) {
            $recommended = $blocks[0];
        } elseif (!empty($availableHours)) {
            $recommended = array_slice($availableHours, 0, $minDuration);
        }
        
        return $recommended;
    }
    
    private function findConsecutiveBlocks(array $hours, int $minLength): array
    {
        sort($hours);
        $blocks = [];
        $currentBlock = [];
        
        foreach ($hours as $hour) {
            if (empty($currentBlock) || $hour === end($currentBlock) + 1) {
                $currentBlock[] = $hour;
            } else {
                if (count($currentBlock) >= $minLength) {
                    $blocks[] = $currentBlock;
                }
                $currentBlock = [$hour];
            }
        }
        
        if (count($currentBlock) >= $minLength) {
            $blocks[] = $currentBlock;
        }
        
        return $blocks;
    }
}
