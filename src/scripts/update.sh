#!/bin/bash

echo "========================================="
echo "Sistema de Actualizacion Manual"
echo "========================================="
echo ""

# Colores para output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Verificar que estamos en el directorio correcto
if [ ! -f "docker-compose.yml" ]; then
    echo -e "${RED}Error: Este script debe ejecutarse desde el directorio raiz del proyecto${NC}"
    exit 1
fi

echo -e "${YELLOW}1. Verificando contenedores...${NC}"
if ! docker compose ps | grep -q "Up"; then
    echo -e "${RED}Error: Los contenedores no estan corriendo${NC}"
    echo "Ejecuta: docker compose up -d"
    exit 1
fi
echo -e "${GREEN}Contenedores activos${NC}"
echo ""

echo -e "${YELLOW}2. Verificando permisos de logs...${NC}"
docker compose exec php chmod -R 777 /var/www/html/storage/logs
docker compose exec php chmod -R 777 /var/www/html/storage
echo -e "${GREEN}Permisos actualizados${NC}"
echo ""

echo -e "${YELLOW}3. Procesando datos del dia actual...${NC}"
TODAY=$(date +%Y-%m-%d)
echo "Fecha: $TODAY"
docker compose exec php php /var/www/html/scripts/process-day.php "$TODAY"
if [ $? -eq 0 ]; then
    echo -e "${GREEN}Datos de hoy procesados correctamente${NC}"
else
    echo -e "${RED}Error procesando datos de hoy${NC}"
fi
echo ""

echo -e "${YELLOW}4. Intentando procesar datos de maniana...${NC}"
TOMORROW=$(date -d "+1 day" +%Y-%m-%d)
echo "Fecha: $TOMORROW"
docker compose exec php php /var/www/html/scripts/process-day.php "$TOMORROW" 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}Datos de maniana procesados correctamente${NC}"
else
    echo -e "${YELLOW}Datos de maniana aun no disponibles (normal antes de las 20:15h)${NC}"
fi
echo ""

echo -e "${YELLOW}5. Verificando datos en base de datos...${NC}"
docker compose exec mysql mysql -uapp -psecret app -e "
SELECT 
    price_date as Fecha,
    geo_name as Zona,
    COUNT(*) as Registros
FROM electricity_prices 
WHERE price_date >= CURDATE()
GROUP BY price_date, geo_name;
" 2>/dev/null
echo ""

echo -e "${YELLOW}6. Verificando estado del cron...${NC}"
docker compose exec php crontab -l 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}Cron configurado correctamente${NC}"
else
    echo -e "${RED}Advertencia: Cron no esta configurado${NC}"
fi
echo ""

echo -e "${YELLOW}7. Ultimas 10 lineas del log...${NC}"
docker compose exec php tail -10 /var/www/html/storage/logs/app-$(date +%Y-%m-%d).log 2>/dev/null
echo ""

echo "========================================="
echo -e "${GREEN}Actualizacion completada${NC}"
echo "Fecha y hora: $(date)"
echo "========================================="
