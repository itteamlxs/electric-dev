<?php

namespace App\Models;

class Recommendation extends BaseModel
{
    protected string $table = 'recommendations';
    
    public function findByDateAndTask(string $date, int $taskId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} 
             WHERE price_date = ? AND task_id = ? 
             LIMIT 1"
        );
        $stmt->execute([$date, $taskId]);
        $result = $stmt->fetch();
        
        if ($result && isset($result['recommended_hours'])) {
            $result['recommended_hours'] = json_decode($result['recommended_hours'], true);
        }
        
        return $result ?: null;
    }
    
    public function findByDate(string $date): array
    {
        $stmt = $this->db->prepare(
            "SELECT r.*, t.task_code, t.task_name 
             FROM {$this->table} r
             INNER JOIN tasks t ON r.task_id = t.id
             WHERE r.price_date = ?"
        );
        $stmt->execute([$date]);
        $results = $stmt->fetchAll();
        
        foreach ($results as &$result) {
            if (isset($result['recommended_hours'])) {
                $result['recommended_hours'] = json_decode($result['recommended_hours'], true);
            }
        }
        
        return $results;
    }
    
    public function insertOrUpdate(int $taskId, string $date, array $hours): bool
    {
        $hoursJson = json_encode($hours);
        
        $sql = "INSERT INTO {$this->table} (task_id, price_date, recommended_hours) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE recommended_hours = VALUES(recommended_hours)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$taskId, $date, $hoursJson]);
    }
}
