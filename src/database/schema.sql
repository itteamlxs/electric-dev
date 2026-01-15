-- Tabla de precios horarios de electricidad
CREATE TABLE IF NOT EXISTS electricity_prices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    price_date DATE NOT NULL,
    hour TINYINT UNSIGNED NOT NULL,
    price_eur_mwh DECIMAL(10, 5) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_date_hour (price_date, hour),
    INDEX idx_date (price_date),
    INDEX idx_price (price_eur_mwh)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de clasificaciones horarias
CREATE TABLE IF NOT EXISTS hour_classifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    price_date DATE NOT NULL,
    hour TINYINT UNSIGNED NOT NULL,
    classification ENUM('buena', 'normal', 'cara') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_date_hour (price_date, hour),
    INDEX idx_date_classification (price_date, classification)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de tareas del hogar
CREATE TABLE IF NOT EXISTS tasks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_code VARCHAR(50) NOT NULL UNIQUE,
    task_name VARCHAR(100) NOT NULL,
    min_duration_hours TINYINT UNSIGNED NOT NULL DEFAULT 1,
    high_consumption BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de recomendaciones por tarea
CREATE TABLE IF NOT EXISTS recommendations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id INT UNSIGNED NOT NULL,
    price_date DATE NOT NULL,
    recommended_hours JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    UNIQUE KEY unique_task_date (task_id, price_date),
    INDEX idx_date (price_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar tareas predefinidas
INSERT INTO tasks (task_code, task_name, min_duration_hours, high_consumption) VALUES
('lavadora', 'Lavadora', 2, true),
('secadora', 'Secadora', 2, true),
('horno', 'Horno', 1, true),
('lavavajillas', 'Lavavajillas', 2, true);
