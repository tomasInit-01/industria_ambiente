# Funcionalidad de Gestión de Clientes

## Resumen
Se ha implementado la funcionalidad completa para crear registros de clientes en la aplicación de muestreo. El sistema permite crear clientes con información completa tanto en la pestaña General como en la de Facturación.

## Características Implementadas

### 1. Modelos Creados
- **Clientes**: Modelo principal para la tabla `cli`
- **CondicionIva**: Modelo para la tabla `civa` (condiciones de IVA)
- **Zona**: Modelo para la tabla `zon` (zonas geográficas)
- **CondicionPago**: Modelo para la tabla `pag` (condiciones de pago)

### 2. Funcionalidades del Formulario

#### Pestaña General
- **Código**: Se genera automáticamente si no se proporciona
- **Razón Social**: Campo obligatorio
- **Fantasía**: Campo opcional
- **Dirección y Localidad**: Información de ubicación
- **Código Postal**: Campo opcional
- **País**: Por defecto "ARG" (Argentina)
- **Provincia**: Código de provincia
- **Zona**: Código de zona geográfica
- **Autoriza**: Persona que autoriza
- **Rubro**: Código de rubro de la empresa
- **Número de Carpeta**: Campo opcional
- **Documentación**: Observaciones generales
- **Zona Comercial**: Campo opcional
- **Promotor**: Código de promotor
- **Fechas**: Fecha de alta (por defecto hoy)
- **Estado**: Activo/Inactivo (por defecto Activo)

#### Pestaña Facturación
- **Condición de IVA**: Código y descripción
- **Condición de Pago**: Campo de texto libre
- **Tipo**: Código y descripción del tipo de cliente
- **Lista de Precios**: Campo opcional
- **Número de Lista de Precios**: Selector (1, 2, 3)
- **CUIT/CUIL/DNI**: Tipo de documento y número
- **Tipo de Factura**: A, B, o C
- **Descuento**: Porcentaje de descuento global
- **Factores por Sector**: Tabla con porcentajes para:
  - Laboratorio
  - Higiene y Seguridad
  - Microbiología
  - Cromatografía
- **Observaciones**: Campo de texto libre para observaciones de facturación

### 3. Validaciones Implementadas
- **Razón Social**: Obligatorio, máximo 255 caracteres
- **Estado**: Obligatorio (Activo/Inactivo)
- **Formato de campos**: Se aplica padding automático según la estructura de la base de datos
- **Manejo de errores**: Mensajes de error claros y específicos

### 4. Funcionalidades Técnicas

#### Generación Automática de Código
- Si no se proporciona código, se genera automáticamente incrementando el último código existente
- Los códigos se almacenan con padding de espacios (10 caracteres)

#### Manejo de Sectores
- Los datos de los sectores (Laboratorio, Higiene, Microbiología, Cromatografía) se almacenan como JSON en el campo de observaciones
- Cada sector incluye: porcentaje, contacto y observaciones específicas

#### Formato de Datos
- Los campos de texto se almacenan con padding de espacios según las especificaciones de la base de datos
- Las fechas se manejan correctamente
- Los valores numéricos se convierten apropiadamente

## Uso del Sistema

### Para Crear un Cliente:
1. Navegar a `/clientes/create`
2. Completar los campos obligatorios (mínimo Razón Social)
3. Usar las pestañas "General" y "Facturación" para completar la información
4. Hacer clic en "Guardar"

### Campos Obligatorios Mínimos:
- Razón Social
- Estado (Activo/Inactivo)

### Campos con Valores por Defecto:
- Código: Se genera automáticamente
- País: "ARG" (Argentina)
- Fecha de Alta: Fecha actual
- Estado: Activo
- Número de Lista de Precios: 1

## Estructura de Base de Datos

El sistema trabaja con la tabla `cli` existente y utiliza las siguientes tablas relacionadas:
- `civa`: Condiciones de IVA
- `zon`: Zonas geográficas  
- `pag`: Condiciones de pago

## Mensajes del Sistema

### Éxito:
- "Cliente creado exitosamente con código: [CÓDIGO]"

### Errores:
- Validación de campos obligatorios
- Errores de base de datos
- Mensajes específicos para cada tipo de error

## Rutas Disponibles

- `GET /clientes`: Listado de clientes
- `GET /clientes/create`: Formulario de creación
- `POST /clientes`: Almacenar nuevo cliente
- `GET /clientes/{id}/edit`: Formulario de edición
- `PUT /clientes/{id}`: Actualizar cliente
- `DELETE /clientes/{id}`: Eliminar cliente

## Logs del Sistema

Todas las operaciones se registran en los logs de Laravel:
- Creación exitosa de clientes
- Errores durante la creación
- Información de debugging

## Compatibilidad

El sistema es compatible con:
- Laravel (versión del proyecto)
- Base de datos PostgreSQL (estructura existente)
- Bootstrap 5 (interfaz de usuario)
- Heroicons (iconografía)

## Próximas Mejoras Sugeridas

1. **Validación de CUIT**: Implementar validación del formato de CUIT
2. **Búsqueda de Códigos**: Implementar funcionalidad para los botones de búsqueda (lupa)
3. **Autocompletado**: Agregar autocompletado para códigos de zona, rubro, etc.
4. **Historial**: Mantener historial de cambios en los clientes
5. **Importación**: Permitir importación masiva de clientes desde Excel/CSV

