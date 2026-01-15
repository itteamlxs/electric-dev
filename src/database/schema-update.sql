-- Agregar columna geo_id a electricity_prices
ALTER TABLE electricity_prices 
ADD COLUMN geo_id INT UNSIGNED DEFAULT 8741 AFTER hour,
ADD COLUMN geo_name VARCHAR(50) DEFAULT 'Península' AFTER geo_id,
DROP INDEX unique_date_hour,
ADD UNIQUE KEY unique_date_hour_geo (price_date, hour, geo_id);

-- Agregar columna geo_id a hour_classifications
ALTER TABLE hour_classifications
ADD COLUMN geo_id INT UNSIGNED DEFAULT 8741 AFTER hour,
DROP INDEX unique_date_hour,
ADD UNIQUE KEY unique_date_hour_geo (price_date, hour, geo_id);

-- Agregar columna geo_id a recommendations
ALTER TABLE recommendations
ADD COLUMN geo_id INT UNSIGNED DEFAULT 8741 AFTER task_id,
DROP INDEX unique_task_date,
ADD UNIQUE KEY unique_task_date_geo (task_id, price_date, geo_id);

-- Crear tabla de zonas geográficas
CREATE TABLE IF NOT EXISTS geographic_zones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    geo_id INT UNSIGNED NOT NULL UNIQUE,
    geo_name VARCHAR(50) NOT NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar zonas
INSERT INTO geographic_zones (geo_id, geo_name) VALUES
(8741, 'Península'),
(8742, 'Canarias'),
(8743, 'Baleares'),
(8744, 'Ceuta'),
(8745, 'Melilla');
