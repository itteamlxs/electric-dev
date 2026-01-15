<?php

namespace App\Models;

class HourClassification extends BaseModel
{
    protected string $table = 'hour_classifications';
    
    public function findByDate(string $date, ?int $geoId = 8741): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE price_date = ?";
        $params = [$date];
        
        if ($geoId !== null) {
            $sql .= " AND geo_id = ?";
            $params[] = $geoId;
        }
        
        $sql .= " ORDER BY hour ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function insertBulk(array $classifications): bool
    {
        $sql = "INSERT INTO {$this->table} (price_date, hour, geo_id, classification) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE classification = VALUES(classification)";
        
        $stmt = $this->db->prepare($sql);
        
        $this->db->beginTransaction();
        
        try {
            foreach ($classifications as $item) {
                $stmt->execute([
                    $item['price_date'],
                    $item['hour'],
                    $item['geo_id'],
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
    
    public function getByDateAndClassification(string $date, string $classification, int $geoId = 8741): array
    {
        $stmt = $this->db->prepare(
            "SELECT hour FROM {$this->table} 
             WHERE price_date = ? AND classification = ? AND geo_id = ?
             ORDER BY hour ASC"
        );
        $stmt->execute([$date, $classification, $geoId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}
