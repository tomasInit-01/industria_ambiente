# Migración de Base de Datos - Reestructuración de Tablas

## Resumen de Cambios

Este documento describe la migración completa de la base de datos para implementar la nueva estructura propuesta, eliminando columnas obsoletas y agregando nuevas funcionalidades.

## ⚠️ IMPORTANTE - RESPALDO OBLIGATORIO

**ANTES DE EJECUTAR CUALQUIER MIGRACIÓN:**
1. Crear respaldo completo de la base de datos
2. Verificar que el respaldo sea funcional
3. Tener un plan de rollback preparado

```bash
# Crear respaldo de PostgreSQL
pg_dump -h localhost -U usuario -d muestreo_app > backup_$(date +%Y%m%d_%H%M%S).sql
```

## Archivos Creados

### Migraciones (en orden de ejecución)
1. `2025_10_19_000001_remove_obsolete_columns_from_coti_table.php`
2. `2025_10_19_000002_remove_obsolete_columns_from_cotio_table.php`
3. `2025_10_19_000003_add_new_columns_to_cotio_table.php`
4. `2025_10_19_000004_create_metodos_muestreo_table.php`
5. `2025_10_19_000005_create_metodos_analisis_table.php`
6. `2025_10_19_000006_create_leyes_normativas_table.php`
7. `2025_10_19_000007_add_foreign_keys_to_cotio_table.php`

### Modelos
- `app/Models/MetodoMuestreo.php`
- `app/Models/MetodoAnalisis.php`
- `app/Models/LeyNormativa.php`
- `app/Models/Cotio.php` (actualizado)

### Seeders
- `database/seeders/MetodosYNormativasSeeder.php`
- `database/seeders/DatabaseSeeder.php` (actualizado)

## Cambios Detallados

### Tabla `coti` - Columnas Eliminadas
```sql
-- Columnas que se eliminarán:
coti_solensayo, coti_remito, coti_importe, coti_sector, coti_codigopag,
coti_usos, coti_codigodiv, coti_paridad, coti_codigolp, coti_nroprecio,
coti_vigencia, coti_factor, coti_interes, coti_iva, coti_impint,
coti_perciva, coti_iibb, coti_ganancias, coti_acre, coti_dto1,
coti_dto2, coti_mail2, coti_mail3, coti_id, coti_nrooc, coti_abono,
coti_codigoclif, coti_codigosucf
```

### Tabla `cotio` - Cambios Principales

#### Columnas Eliminadas
```sql
-- Columnas que se eliminarán:
cotio_codigonormativa, cotio_estado, fecha_inicio, fecha_fin,
vehiculo_asignado, es_frecuente, frecuencia_dias, enable_ot,
modulo_origen, active_ot, cotio_identificacion, muestreo_contador,
volumen_muestra

-- Nota: cotio_responsable_codigo se mantiene (no se elimina)
```

#### Columnas Agregadas
```sql
-- Nuevas columnas:
cotio_codigometodo_analisis CHAR(15) -- FK a metodos_analisis.codigo
limite_deteccion DECIMAL(15,6)
limite_cuantificacion DECIMAL(15,6)
ley_aplicacion VARCHAR(255) -- FK a leyes_normativas.codigo

-- Nota: La columna cotio_codigometodo existente se reutiliza para métodos de muestreo
```

### Nuevas Tablas

#### `metodos_muestreo`
- Catálogo de métodos de muestreo disponibles
- Incluye: código, nombre, descripción, equipo requerido, procedimiento, costo base

#### `metodos_analisis`
- Catálogo de métodos de análisis disponibles
- Incluye: límites de detección/cuantificación por defecto, tiempo estimado, calibración requerida

#### `leyes_normativas`
- Catálogo de leyes y normativas aplicables
- Incluye: grupos (ej: Código Alimentario Argentino), artículos, variables aplicables

## Instrucciones de Migración

### Paso 1: Preparación
```bash
# 1. Crear respaldo
pg_dump -h localhost -U usuario -d muestreo_app > backup_pre_migracion.sql

# 2. Verificar que no hay migraciones pendientes
php artisan migrate:status

# 3. Verificar integridad de datos críticos
# (ejecutar consultas de verificación según sea necesario)
```

### Paso 2: Ejecutar Migraciones
```bash
# Ejecutar todas las migraciones en orden
php artisan migrate

# Verificar que todas las migraciones se ejecutaron correctamente
php artisan migrate:status
```

### Paso 3: Poblar Datos Iniciales
```bash
# Ejecutar seeders para datos iniciales
php artisan db:seed --class=MetodosYNormativasSeeder

# O ejecutar todos los seeders
php artisan db:seed
```

### Paso 4: Verificación Post-Migración
```bash
# Verificar estructura de tablas
php artisan tinker
>>> Schema::hasTable('metodos_muestreo')
>>> Schema::hasTable('metodos_analisis')
>>> Schema::hasTable('leyes_normativas')

# Verificar datos de ejemplo
>>> App\Models\MetodoMuestreo::count()
>>> App\Models\MetodoAnalisis::count()
>>> App\Models\LeyNormativa::count()
```

## Plan de Rollback

En caso de problemas, ejecutar en orden inverso:

```bash
# 1. Rollback de migraciones (una por una desde la más reciente)
php artisan migrate:rollback --step=1

# 2. Si es necesario rollback completo:
php artisan migrate:rollback --step=7

# 3. Restaurar desde respaldo si es necesario:
psql -h localhost -U usuario -d muestreo_app < backup_pre_migracion.sql
```

## Impacto en el Código Existente

### ⚠️ CÓDIGO QUE REQUIERE ACTUALIZACIÓN

Las siguientes funcionalidades **DEJARÁN DE FUNCIONAR** después de la migración:

1. **Controladores afectados:**
   - `CotioController.php` - métodos que usan `enable_ot`, `cotio_estado`
   - `OrdenController.php` - filtros por estado
   - `MuestrasController.php` - asignación de vehículos
   - `DashboardController.php` - dashboard de análisis

2. **Modelos afectados:**
   - `Cotio.php` - relaciones con vehículos, estados
   - `CotioInstancia.php` - campos eliminados

3. **Vistas afectadas:**
   - Todas las vistas que muestren estados, vehículos asignados, etc.

### Nuevas Funcionalidades Disponibles

1. **Selección de métodos por catálogo**
2. **Aplicación automática de límites de detección**
3. **Vinculación con normativas específicas**
4. **Gestión centralizada de métodos y leyes**

## Datos de Ejemplo Incluidos

### Métodos de Muestreo
- MUE001: Muestreo Manual Simple
- MUE002: Muestreo Automático
- MUE003: Muestreo Compuesto

### Métodos de Análisis
- ANA001: Análisis Fisicoquímico Básico
- ANA002: Cromatografía Líquida HPLC
- ANA003: Espectrofotometría UV-Vis
- ANA004: Análisis Microbiológico

### Normativas
- CAA982: Código Alimentario Argentino - Art. 982
- CAA983: Código Alimentario Argentino - Art. 983
- LEY24051: Ley Nacional de Residuos Peligrosos
- RES831: Resolución 831/93 - Niveles Guía de Calidad de Agua

## Contacto y Soporte

Para dudas o problemas durante la migración, contactar al equipo de desarrollo.

---
**Fecha de creación:** 19 de Octubre, 2025
**Versión:** 1.0
