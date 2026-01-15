<?php

namespace App\Models;

class ElectricityPrice extends BaseModel
{
    protected string $table = 'electricity_prices';
    
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
    
    public function insertBulk(array $prices): bool
    {
        $sql = "INSERT INTO {$this->table} (price_date, hour, geo_id, geo_name, price_eur_mwh) 
                VALUES (?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE price_eur_mwh = VALUES(price_eur_mwh), geo_name = VALUES(geo_name)";
        
        $stmt = $this->db->prepare($sql);
        
        $this->db->beginTransaction();
        
        try {
            foreach ($prices as $price) {
                $stmt->execute([
                    $price['price_date'],
                    $price['hour'],
                    $price['geo_id'],
                    $price['geo_name'],
                    $price['price_eur_mwh']
                ]);
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getStatsByDate(string $date, int $geoId = 8741): array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                MIN(price_eur_mwh) as min_price,
                MAX(price_eur_mwh) as max_price,
                AVG(price_eur_mwh) as avg_price
            FROM {$this->table} 
            WHERE price_date = ? AND geo_id = ?"
        );
        $stmt->execute([$date, $geoId]);
        return $stmt->fetch();
    }
    
    public function getAvailableZones(string $date): array
    {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT geo_id, geo_name 
             FROM {$this->table} 
             WHERE price_date = ? 
             ORDER BY geo_id ASC"
        );
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    }
}
