# Workflow de Calidad de Código - Laravel Project

Este proyecto utiliza un stack completo de herramientas para mantener alta calidad de código mediante análisis estático, formateo automático y cumplimiento de estándares.

## 🛠️ Herramientas Configuradas

### 1. **PHP CS Fixer** - Corrección Automática
- **Función:** Corrige formato y moderniza código automáticamente
- **Archivo config:** `.php-cs-fixer.php`
- **Estándares:** PSR-12 + PHP 8.3 modernization + Laravel best practices

### 2. **PHP_CodeSniffer** - Verificación de Estándares  
- **Función:** Detecta violaciones de estándares de codificación
- **Archivo config:** `phpcs.xml`
- **Estándares:** PSR-12 customizado para Laravel

### 3. **PHPStan** - Análisis Estático
- **Función:** Detecta errores de tipos, lógica y bugs potenciales
- **Archivo config:** `phpstan.neon`
- **Extensión:** Larastan para soporte específico de Laravel

## 🔄 Workflow Recomendado

### Orden de ejecución:
1. **PHP CS Fixer** → Arregla formato y moderniza
2. **PHP_CodeSniffer** → Verifica cumplimiento de estándares  
3. **PHPStan** → Analiza tipos y lógica

### ¿Por qué este orden?
- PHPStan analiza código ya formateado → reportes más claros
- PHP CS Fixer corrige problemas que phpcs también detectaría
- Cada herramienta se enfoca en su especialidad

## 📋 Comandos Disponibles

### 🔧 **Corrección Automática**
```bash
# Aplicar PHP CS Fixer
composer cs-fix

# Ver qué cambiaría PHP CS Fixer (sin aplicar)
composer cs-fix:dry

# Aplicar correcciones básicas de phpcs
composer lint:fix

# Aplicar ambos: cs-fix + lint:fix
composer fix
```

### ✅ **Verificación**
```bash
# Verificar estándares con phpcs
composer lint

# Resumen de errores de phpcs
composer lint:summary

# Análisis estático con PHPStan
composer analyse

# Ver todos los problemas sin corregir
composer check
```

### 🎯 **Workflows Completos**

#### Desarrollo Diario
```bash
composer quality
```
**Ejecuta:** cs-fix → lint → analyse  
**Propósito:** Mantener código limpio durante desarrollo

#### Antes de Commit
```bash
composer quality:check
```
**Ejecuta:** cs-fix:dry → lint → analyse  
**Propósito:** Verificar estado sin modificar archivos

#### Para CI/CD
```bash
composer quality:ci
```
**Ejecuta:** cs-fix:dry → lint:summary → analyse  
**Propósito:** Pipeline optimizado, falla si hay errores

#### Limpieza Completa
```bash
composer quality:full
```
**Ejecuta:** cs-fix → lint:fix → lint → analyse  
**Propósito:** Arreglar todo lo posible automáticamente

### 🔍 **PHPStan Específico**
```bash
# Crear baseline de errores existentes
composer analyse:baseline

# Limpiar cache
composer analyse:clear

# Analizar con nivel específico
composer analyse:level
```

## 📊 Comparación de Herramientas

| Problema | PHP CS Fixer | PHP_CodeSniffer | PHPStan |
|----------|--------------|-----------------|---------|
| `'Hello'.'World'` | ✅ Corrige | ✅ Detecta | ❌ |
| `const PER_PAGE = 20` (sin tipo) | ❌ | ❌ | ✅ Detecta |
| `$user->badProperty` | ❌ | ❌ | ✅ Detecta |
| `function($undefined)` | ❌ | Parcial | ✅ Detecta |
| `return 'string'` en función tipada `int` | ❌ | ❌ | ✅ Detecta |
| `is_null($var)` | ✅ → `$var === null` | ❌ | ❌ |
| `pow(2, 3)` | ✅ → `2 ** 3` | ❌ | ❌ |
| Espacios después de cast | ✅ Corrige | ✅ Detecta | ❌ |

## 🚀 Integración en Desarrollo

### Editor (VSCode)
```json
{
    "[php]": {
        "editor.defaultFormatter": "junstyle.php-cs-fixer",
        "editor.formatOnSave": true
    },
    "phpstan.enabled": true,
    "phpstan.configFile": "phpstan.neon"
}
```

### Git Hooks (Opcional)
```bash
# .git/hooks/pre-commit
#!/bin/bash
composer quality:check
```

### CI/CD Pipeline
```yaml
# GitHub Actions / GitLab CI
- name: Code Quality
  run: composer quality:ci
```

## 📝 Configuraciones

### PHP CS Fixer (`.php-cs-fixer.php`)
- ✅ PSR-12 base
- ✅ PHP 8.3 modernization  
- ✅ Array syntax moderna
- ✅ Concatenación con espacios
- ✅ Imports ordenados
- ✅ Reglas específicas para Laravel

### PHP_CodeSniffer (`phpcs.xml`)
- ✅ PSR-12 estricto
- ✅ Excluye directorios irrelevantes (vendor, storage)
- ✅ Permite `snake_case` en tests
- ✅ Líneas más largas en migraciones
- ✅ Ignora errores comunes de Laravel/Livewire/Filament

### PHPStan (`phpstan.neon`)
- ✅ Nivel 3 (intermedio)
- ✅ Larastan para soporte Laravel
- ✅ Ignora magic methods de Eloquent
- ✅ Soporte para Livewire y Filament
- ✅ Bootstrap de Laravel cargado

## 🎯 Uso Diario Recomendado

### Durante desarrollo:
```bash
composer quality  # Una vez al día
```

### Antes de commit:
```bash
composer quality:check  # Verificar estado
```

### Limpieza semanal:
```bash
composer quality:full  # Limpieza profunda
```

### Comandos rápidos:
- `composer fix` → Formato rápido
- `composer check` → Estado general

## 🔧 Troubleshooting

### Cache corrupto:
```bash
composer analyse:clear
rm -rf .phpcs-cache .php-cs-fixer.cache
```

### Demasiados errores:
```bash
# Crear baseline y trabajar incrementalmente
composer analyse:baseline
```

### Conflictos entre herramientas:
```bash
# Ejecutar por separado para identificar origen
composer cs-fix:dry
composer lint
composer analyse
```

## 📈 Niveles de PHPStan

- **Nivel 0-2:** Básico (errores sintácticos)
- **Nivel 3-5:** Intermedio (recomendado) ← **Actual**
- **Nivel 6-7:** Avanzado (tipos más estrictos)  
- **Nivel 8-9:** Experto (muy estricto)

Para aumentar nivel gradualmente:
```bash
composer analyse:level  # Probar nivel 5
# Editar phpstan.neon: level: 5
```

## 🎉 Beneficios

- ✅ **Código consistente** en todo el equipo
- ✅ **Detección temprana** de bugs
- ✅ **Modernización automática** del código
- ✅ **Cumplimiento** de estándares PSR-12
- ✅ **Integración** con CI/CD
- ✅ **Workflow** automatizado y eficiente

---

**Última actualización:** Configurado para Laravel 11 + PHP 8.3 + Filament + Livewire