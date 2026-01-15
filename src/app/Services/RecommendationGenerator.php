<?php

namespace App\Services;

use App\Models\Task;
use App\Models\HourClassification;
use App\Models\Recommendation;
use App\Config\Logger;

class RecommendationGenerator
{
    private Task $taskModel;
    private HourClassification $classificationModel;
    private Recommendation $recommendationModel;
    
    public function __construct()
    {
        $this->taskModel = new Task();
        $this->classificationModel = new HourClassification();
        $this->recommendationModel = new Recommendation();
    }
    
    public function generateForDate(string $date): bool
    {
        Logger::info("Generating recommendations for date: $date");
        
        $tasks = $this->taskModel->getAllActive();
        
        if (empty($tasks)) {
            Logger::error("No tasks found");
            return false;
        }
        
        // Obtener horas buenas
        $goodHours = $this->classificationModel->getByDateAndClassification($date, 'buena');
        
        if (empty($goodHours)) {
            Logger::warning("No good hours found for $date, using normal hours");
            $goodHours = $this->classificationModel->getByDateAndClassification($date, 'normal');
        }
        
        // Generar recomendaciones por tarea
        foreach ($tasks as $task) {
            $recommendedHours = $this->selectHoursForTask($task, $goodHours);
            
            try {
                $this->recommendationModel->insertOrUpdate(
                    $task['id'],
                    $date,
                    $recommendedHours
                );
            } catch (\Exception $e) {
                Logger::error("Failed to save recommendation for task {$task['task_code']}: " . $e->getMessage());
                return false;
            }
        }
        
        Logger::info("Successfully generated recommendations for " . count($tasks) . " tasks");
        return true;
    }
    
    private function selectHoursForTask(array $task, array $availableHours): array
    {
        $minDuration = $task['min_duration_hours'];
        $recommended = [];
        
        // Buscar bloques consecutivos
        $blocks = $this->findConsecutiveBlocks($availableHours, $minDuration);
        
        if (!empty($blocks)) {
            // Tomar el primer bloque disponible
            $recommended = $blocks[0];
        } elseif (!empty($availableHours)) {
            // Si no hay bloques, tomar las primeras horas disponibles
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
