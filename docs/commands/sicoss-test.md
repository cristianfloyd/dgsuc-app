# Comando SICOSS Test

El comando `sicoss:test` permite probar la generación de datos SICOSS para un legajo específico sin crear archivos TXT, ideal para debugging y testing.

## 🚀 Uso Básico

```bash
php artisan sicoss:test {legajo}
```

**Ejemplo**:

```bash
php artisan sicoss:test 12345
```

## 📋 Parámetros y Opciones

### Parámetro Requerido

- `legajo`: Número de legajo a procesar

### Opciones Disponibles

| Opción | Descripción | Ejemplo |
|--------|-------------|---------|
| `--periodo=YYYY-MM` | Período fiscal específico | `--periodo=2024-10` |
| `--connection=conexion` | Conexión de BD a usar | `--connection=pgsql-liqui` |
| `--retro` | Incluir períodos retroactivos | `--retro` |
| `--licencias` | Incluir procesamiento de licencias | `--licencias` |
| `--inactivos` | Incluir agentes sin cargo activo | `--inactivos` |
| `--seguro-vida` | Incluir seguro de vida patronal | `--seguro-vida` |
| `--export=archivo.json` | Exportar resultado a JSON | `--export=resultado.json` |
| `--detailed` | Mostrar información detallada | `--detailed` |
| `--clean-only` | Solo limpiar tablas temporales | `--clean-only` |

## 🔧 Ejemplos de Uso

### Básico - Solo un legajo

```bash
php artisan sicoss:test 12345
```

### Con todas las opciones

```bash
php artisan sicoss:test 12345 --retro --licencias --inactivos --seguro-vida --detailed
```

### Cambiar conexión de base de datos

```bash
# Usar base de datos de liquidaciones
php artisan sicoss:test 12345 --connection=pgsql-liqui

# Usar base de datos de desarrollo
php artisan sicoss:test 12345 --connection=pgsql-desa

# Usar base de datos de producción (por defecto)
php artisan sicoss:test 12345 --connection=pgsql-prod
```

### Especificar período

```bash
php artisan sicoss:test 12345 --periodo=2024-10
```

> **Nota**: El comando advertirá que el período debe configurarse desde la interfaz de Mapuche

### Exportar resultados a JSON

```bash
php artisan sicoss:test 12345 --export=sicoss_12345.json --detailed
```

### Solo limpiar tablas temporales

```bash
php artisan sicoss:test --clean-only
```

### Ejemplo completo con conexión específica

```bash
php artisan sicoss:test 12345 --connection=pgsql-liqui --retro --detailed --export=test_liqui.json
```

## 🔗 Conexiones de Base de Datos Disponibles

| Conexión | Descripción | Uso Recomendado |
|----------|-------------|-----------------|
| `pgsql-prod` | Base de datos de producción | **Por defecto** - Datos oficiales |
| `pgsql-liqui` | Base de datos de liquidaciones | Testing con datos de liquidación |
| `pgsql-desa` | Base de datos de desarrollo | Testing en entorno de desarrollo |
| `pgsql-mapuche` | Base de datos Mapuche legacy | Compatibilidad con sistema anterior |

### Características por Conexión

- **pgsql-prod**: Puerto 5434, schema `mapuche,suc`
- **pgsql-liqui**: Puerto 5433, schema `mapuche,suc`  
- **pgsql-desa**: Puerto 5432, schema `mapuche,suc`
- **pgsql-mapuche**: Puerto 5432, schema `mapuche,suc`

## 📊 Información Mostrada

### Configuración Actual

- Legajo procesado
- Conexión de base de datos activa
- Período fiscal activo
- Código de reparto
- Estado de opciones (retro, licencias, etc.)

### Información del Legajo

- CUIL
- Nombre y apellido
- Código de situación
- Días trabajados
- Obra social
- Información familiar (hijos, cónyuge)

### Importes Principales

- Importe bruto
- Importe imponible principal
- Importe imponible patronal
- SAC (Sueldo Anual Complementario)

### Detalles Adicionales (con `--detailed`)

- Horas extras
- Zona desfavorable
- Adicionales
- Importes imponibles específicos (4, 5, 6, 9)
- Campos adicionales con valores

## 📁 Exportación JSON

Cuando usas `--export`, el archivo JSON incluye:

```json
{
  "timestamp": "2024-01-15T10:30:00.000Z",
  "legajo": 12345,
  "periodo": "10/2024",
  "conexion_bd": "pgsql-liqui",
  "configuracion": {
    "retro": true,
    "licencias": false,
    "inactivos": false,
    "seguro_vida": true,
    "connection": "pgsql-liqui"
  },
  "resultado": [
    {
      "cuil": "20123456789",
      "apyno": "DOE, JOHN",
      "IMPORTE_BRUTO": 150000.00,
      // ... más campos
    }
  ]
}
```

## 🛠️ Resolución de Problemas

### Error: Tabla temporal ya existe

```bash
# Limpiar tablas manualmente
php artisan sicoss:test --clean-only

# Luego ejecutar el test
php artisan sicoss:test 12345
```

### Error: Código de reparto NULL

- Verificar configuración en base de datos Mapuche
- El comando usa valor por defecto '1' si no está configurado

### Sin datos para el legajo

Posibles causas:

- El legajo no existe o está inactivo
- No tiene liquidaciones en el período
- No cumple con los filtros aplicados

## 🔍 Debug Avanzado

### Ver información detallada de errores

```bash
php artisan sicoss:test 12345 --detailed
```

### Verificar configuración

```bash
php artisan sicoss:test 12345 --detailed | head -20
```

### Probar múltiples legajos

```bash
# Script bash para probar varios legajos
for legajo in 12345 67890 11111; do
  echo "Testing legajo: $legajo"
  php artisan sicoss:test $legajo --export="sicoss_${legajo}.json"
done
```

## 📝 Notas Importantes

1. **No genera archivos TXT**: Este comando solo procesa datos para visualización
2. **Limpieza automática**: Las tablas temporales se limpian automáticamente
3. **Configuración actual**: Usa la configuración activa de Mapuche
4. **Período fiscal**: Debe configurarse desde la interfaz de Mapuche
5. **Conexión requerida**: Necesita acceso a la base de datos Mapuche

## 🚦 Códigos de Salida

- `0`: Éxito
- `1`: Error (legajo inválido, error de procesamiento, etc.)

## 🔗 Ver También

- [Documentación SICOSS](../afip/Sicoss_Legacy_refactor.md)
- [Configuración Mapuche](../mapuche/configuracion.md)
- [Comandos Artisan](../commands/README.md) 
