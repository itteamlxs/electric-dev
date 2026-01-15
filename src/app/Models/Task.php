<?php

namespace App\Models;

class Task extends BaseModel
{
    protected string $table = 'tasks';
    
    public function findByCode(string $code): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE task_code = ? LIMIT 1"
        );
        $stmt->execute([$code]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function getAllActive(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM {$this->table} ORDER BY task_name ASC"
        );
        return $stmt->fetchAll();
    }
}
