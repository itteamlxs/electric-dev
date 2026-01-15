<?php

namespace App\Models;

class ElectricityPrice extends BaseModel
{
    protected string $table = 'electricity_prices';
    
    public function findByDate(string $date): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE price_date = ? ORDER BY hour ASC"
        );
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    }
    
    public function insertBulk(array $prices): bool
    {
        $sql = "INSERT INTO {$this->table} (price_date, hour, price_eur_mwh) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE price_eur_mwh = VALUES(price_eur_mwh)";
        
        $stmt = $this->db->prepare($sql);
        
        $this->db->beginTransaction();
        
        try {
            foreach ($prices as $price) {
                $stmt->execute([
                    $price['price_date'],
                    $price['hour'],
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
    
    public function getStatsByDate(string $date): array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                MIN(price_eur_mwh) as min_price,
                MAX(price_eur_mwh) as max_price,
                AVG(price_eur_mwh) as avg_price
            FROM {$this->table} 
            WHERE price_date = ?"
        );
        $stmt->execute([$date]);
        return $stmt->fetch();
    }
}
