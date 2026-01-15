-- Tabla de precios horarios de electricidad
CREATE TABLE IF NOT EXISTS electricity_prices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    price_date DATE NOT NULL,
    hour TINYINT UNSIGNED NOT NULL,
    geo_id INT UNSIGNED NOT NULL DEFAULT 8741,
    geo_name VARCHAR(50) NOT NULL DEFAULT 'Península',
    price_eur_mwh DECIMAL(10, 5) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_date_hour_geo (price_date, hour, geo_id),
    INDEX idx_date_geo (price_date, geo_id),
    INDEX idx_geo_date (geo_id, price_date),
    INDEX idx_price (price_eur_mwh),
    INDEX idx_date_hour (price_date, hour)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de clasificaciones horarias
CREATE TABLE IF NOT EXISTS hour_classifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    price_date DATE NOT NULL,
    hour TINYINT UNSIGNED NOT NULL,
    geo_id INT UNSIGNED NOT NULL DEFAULT 8741,
    classification ENUM('buena', 'normal', 'cara') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_date_hour_geo (price_date, hour, geo_id),
    INDEX idx_date_classification_geo (price_date, classification, geo_id),
    INDEX idx_geo_date (geo_id, price_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de tareas del hogar
CREATE TABLE IF NOT EXISTS tasks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_code VARCHAR(50) NOT NULL UNIQUE,
    task_name VARCHAR(100) NOT NULL,
    min_duration_hours TINYINT UNSIGNED NOT NULL DEFAULT 1,
    high_consumption BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_task_code (task_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de recomendaciones por tarea
CREATE TABLE IF NOT EXISTS recommendations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id INT UNSIGNED NOT NULL,
    geo_id INT UNSIGNED NOT NULL DEFAULT 8741,
    price_date DATE NOT NULL,
    recommended_hours JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    UNIQUE KEY unique_task_date_geo (task_id, price_date, geo_id),
    INDEX idx_date_geo (price_date, geo_id),
    INDEX idx_task_date (task_id, price_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de zonas geográficas
CREATE TABLE IF NOT EXISTS geographic_zones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    geo_id INT UNSIGNED NOT NULL UNIQUE,
    geo_name VARCHAR(50) NOT NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar tareas predefinidas
INSERT INTO tasks (task_code, task_name, min_duration_hours, high_consumption) VALUES
('lavadora', 'Lavadora', 2, true),
('secadora', 'Secadora', 2, true),
('horno', 'Horno', 1, true),
('lavavajillas', 'Lavavajillas', 2, true)
ON DUPLICATE KEY UPDATE task_name = VALUES(task_name);

-- Insertar zonas geográficas
INSERT INTO geographic_zones (geo_id, geo_name) VALUES
(8741, 'Península'),
(8742, 'Canarias'),
(8743, 'Baleares'),
(8744, 'Ceuta'),
(8745, 'Melilla')
ON DUPLICATE KEY UPDATE geo_name = VALUES(geo_name);
