# Historial de Versiones - Plataforma Horas Baratas Electricidad

## v1.0.0 - 2026-01-15

### Características Principales
- Sistema completo de recomendación de horas óptimas para consumo eléctrico
- Integración con API oficial de ESIOS (Red Eléctrica Española)
- Soporte para 5 zonas geográficas: Península, Canarias, Baleares, Ceuta, Melilla
- Frontend responsive con selector de zonas
- Actualización automática cada 5 horas vía cron

### Backend
- API REST con 8 endpoints funcionales
- Base de datos MySQL con 4 tablas principales + tabla de zonas geográficas
- Motor de clasificación de precios (buena/normal/cara)
- Generador de recomendaciones por tarea del hogar
- Rate limiting (100 req/hora por IP)
- Sistema de logging completo
- Fallback a datos mock en caso de fallo de API

### Frontend
- Interfaz limpia y responsive
- 4 vistas principales: Hoy, Mañana, Por Tarea, 24 Horas
- Selector de zona geográfica
- Mensajes amigables cuando no hay datos disponibles
- Visualización con códigos de color (verde/amarillo/rojo)

### Seguridad
- Headers de seguridad HTTP implementados
- CORS configurado
- Prepared statements (PDO)
- Validación y sanitización de inputs
- Variables de entorno para datos sensibles
- Rate limiting por IP

### Automatización
- Cron job configurado (cada 5 horas)
- Script manual de actualización (update.sh)
- Script de procesamiento diario (process-day.php)
- Script de importación de precios (import-prices.php)

### Infraestructura
- Docker Compose con 3 contenedores (Nginx, PHP 8.3, MySQL 8.0)
- PHP-FPM optimizado
- Composer para gestión de dependencias
- Autoload PSR-4

### Tareas Soportadas
- Lavadora (2 horas mínimo)
- Secadora (2 horas mínimo)
- Horno (1 hora mínimo)
- Lavavajillas (2 horas mínimo)

### API Endpoints
- GET /api/health - Estado del sistema
- GET /api/today?geo_id={id} - Resumen del día actual
- GET /api/tomorrow?geo_id={id} - Resumen del día siguiente
- GET /api/zones?date={date} - Zonas disponibles
- GET /api/hours?date={date}&geo_id={id} - Precios por hora
- GET /api/task/{task}?date={date}&geo_id={id} - Recomendación por tarea

### Notas Técnicas
- Datos de mañana disponibles después de las 20:15h (hora española)
- Sistema escalable preparado para futuras mejoras
- Código limpio siguiendo estándares PSR
- Sin uso de emojis en código (política del proyecto)

### Dependencias
- vlucas/phpdotenv: ^5.6
- PHP: 8.3
- MySQL: 8.0
- Nginx: Alpine

### Configuración Mínima
- 2GB RAM
- 10GB almacenamiento
- Docker y Docker Compose

---

## v1.0.0 - 2026-01-15

### Características Principales
- Sistema completo de recomendación de horas óptimas para consumo eléctrico
- Integración con API oficial de ESIOS (Red Eléctrica Española)
- Soporte para 5 zonas geográficas: Península, Canarias, Baleares, Ceuta, Melilla
- Frontend responsive con selector de zonas
- Actualización automática cada 5 horas vía cron

### Backend
- API REST con 8 endpoints funcionales
- Base de datos MySQL con 4 tablas principales + tabla de zonas geográficas
- Motor de clasificación de precios (buena / normal / cara)
- Generador de recomendaciones por tarea del hogar
- Rate limiting (100 req/hora por IP)
- Sistema de logging completo
- Fallback a datos mock en caso de fallo de API

### Frontend
- Interfaz limpia y responsive
- 4 vistas principales: Hoy, Mañana, Por Tarea, 24 Horas
- Selector de zona geográfica
- Mensajes amigables cuando no hay datos disponibles
- Visualización con códigos de color (verde / amarillo / rojo)

### Seguridad
- Headers de seguridad HTTP implementados
- CORS configurado
- Prepared statements (PDO)
- Validación y sanitización de inputs
- Variables de entorno para datos sensibles
- Rate limiting por IP

### Automatización
- Cron job configurado (cada 5 horas)
- Script manual de actualización (`update.sh`)
- Script de procesamiento diario (`process-day.php`)
- Script de importación de precios (`import-prices.php`)

### Infraestructura
- Docker Compose con 3 contenedores (Nginx, PHP 8.3, MySQL 8.0)
- PHP-FPM optimizado
- Composer para gestión de dependencias
- Autoload PSR-4

### Tareas Soportadas
- Lavadora (2 horas mínimo)
- Secadora (2 horas mínimo)
- Horno (1 hora mínimo)
- Lavavajillas (2 horas mínimo)

### API Endpoints
- `GET /api/health` — Estado del sistema
- `GET /api/today?geo_id={id}` — Resumen del día actual
- `GET /api/tomorrow?geo_id={id}` — Resumen del día siguiente
- `GET /api/zones?date={date}` — Zonas disponibles
- `GET /api/hours?date={date}&geo_id={id}` — Precios por hora
- `GET /api/task/{task}?date={date}&geo_id={id}` — Recomendación por tarea

### Notas Técnicas
- Datos de mañana disponibles después de las 20:15h (hora española)
- Sistema escalable preparado para futuras mejoras
- Código limpio siguiendo estándares PSR
- Sin uso de emojis en código (política del proyecto)

### Dependencias
- `vlucas/phpdotenv: ^5.6`
- PHP 8.3
- MySQL 8.0
- Nginx Alpine

### Configuración Mínima
- 2GB RAM
- 10GB almacenamiento
- Docker y Docker Compose
  
---

## Roadmap Futuro (v2.0.0)
- Sistema de notificaciones push
- Registro de usuarios
- Historial de ahorro estimado
- Comparativas entre zonas
- Widget para móviles
- API pública documentada con Swagger
- Caché con Redis
- Tests automatizados
