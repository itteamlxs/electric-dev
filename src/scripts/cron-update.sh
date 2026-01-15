#!/bin/bash

cd /var/www/html

# Procesar hoy
php scripts/process-day.php $(date +%Y-%m-%d)

# Intentar procesar mañana (solo funcionará después de 20:15)
php scripts/process-day.php $(date -d "+1 day" +%Y-%m-%d) 2>/dev/null || true

echo "Update completed at $(date)"
