<?php

namespace App\Models;

class HourClassification extends BaseModel
{
    protected string $table = 'hour_classifications';
    
    public function findByDate(string $date): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE price_date = ? ORDER BY hour ASC"
        );
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    }
    
    public function insertBulk(array $classifications): bool
    {
        $sql = "INSERT INTO {$this->table} (price_date, hour, classification) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE classification = VALUES(classification)";
        
        $stmt = $this->db->prepare($sql);
        
        $this->db->beginTransaction();
        
        try {
            foreach ($classifications as $item) {
                $stmt->execute([
                    $item['price_date'],
                    $item['hour'],
                    $item['classification']
                ]);
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getByDateAndClassification(string $date, string $classification): array
    {
        $stmt = $this->db->prepare(
            "SELECT hour FROM {$this->table} 
             WHERE price_date = ? AND classification = ? 
             ORDER BY hour ASC"
        );
        $stmt->execute([$date, $classification]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}
