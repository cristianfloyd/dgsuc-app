# Workflow: Calidad por carpeta (quality:path)

Workflow para mejorar la calidad de código ejecutando `composer quality:path` sobre una carpeta, corrigiendo archivo por archivo hasta dejar la carpeta limpia. Sirve para retomar la tarea en **nuevas conversaciones** sin perder contexto y para **documentar arreglos recurrentes**.

## Objetivo

- Dejar cada carpeta sin errores de PHP CS Fixer, PHP CodeSniffer, Rector y PHPStan.
- Base limpia para poder subir después el nivel de PHPStan en `phpstan.neon`.
- Registrar patrones de corrección que se repiten para aplicarlos con criterio en el futuro.

## Comando principal

```bash
composer quality:path -- <ruta-carpeta-o-archivo>
```

Ejemplos:

```bash
composer quality:path -- app/Repositories/
composer quality:path -- app/ValueObjects/SomeClass.php
```

El script ejecuta en orden: **PHP CS Fixer** (aplica) → **PHP CodeSniffer** (reporta) → **Rector** (aplica) → **PHPStan** (reporta). Ver `scripts/quality-path.sh`.

---

## Estado por carpeta

| Carpeta | Estado | Notas |
|---------|--------|--------|
| `app/Repositories/` | Hecho | Incluye subcarpetas Afip, Sicoss, Mapuche, etc. |
| `app/ValueObjects/` | Hecho | 6 archivos. |
| `app/Exceptions/` | Hecho | 10 archivos. |
| `app/Exports/` | Hecho | Incluye Sheets/, Sicoss/. |
| `app/Services/` | Pendiente | |
| `app/Models/` | Pendiente | |
| `app/Http/` | Pendiente | |
| `app/Data/` | Pendiente | |
| `app/Contracts/` | Pendiente | |
| Otras bajo `app/` | Pendiente | |

Actualizar esta tabla al completar o empezar una carpeta.

---

## Pasos del workflow (por carpeta)

1. **Ejecutar sobre la carpeta**
   ```bash
   composer quality:path -- app/<Carpeta>/
   ```

2. **Revisar la salida** de las 4 herramientas y anotar:
   - Archivos con errores de **CodeSniffer** (y en qué líneas).
   - Archivos con errores de **PHPStan** (y mensaje/tipo).
   - Cambios que aplicó **Rector** (por si hace falta revisar a mano).

3. **Por cada archivo con errores**
   - Ejecutar: `composer quality:path -- app/<Carpeta>/ArchivoConError.php`
   - Corregir según el tipo de error (ver [Arreglos recurrentes](#arreglos-recurrentes)).
   - Repetir hasta que ese archivo pase sin errores.

4. **Verificación final de la carpeta**
   ```bash
   composer quality:path -- app/<Carpeta>/
   ```
   Confirmar que no queden errores.

5. **Formateo con Pint**
   ```bash
   vendor/bin/pint --dirty --format agent
   ```

6. **Opcional:** volver a ejecutar `composer quality:path -- app/<Carpeta>/` tras Pint por si corrige más archivos.

---

## Cómo retomar en una conversación nueva

Decir algo como:

> "Seguir con el workflow de calidad por carpeta. Siguiente carpeta: app/Services/ (o la que toque). Usar docs/workflows/quality-path-by-folder.md."

El agente debe:

1. Leer este archivo.
2. Ejecutar `composer quality:path -- app/<Carpeta>/` para la carpeta indicada.
3. Seguir los pasos del workflow y, al aplicar un mismo tipo de arreglo en **2 o más archivos**, documentarlo en [Arreglos recurrentes](#arreglos-recurrentes).

---

## Trigger: documentar arreglos recurrentes

**Regla:** Si durante este workflow aplicas el **mismo tipo de corrección en 2 o más archivos** (o en 2 o más sitios del mismo archivo), **añade o actualiza** una entrada en la sección [Arreglos recurrentes](#arreglos-recurrentes).

Incluir:

- **Herramienta** que lo reporta (CodeSniffer, PHPStan, etc.).
- **Patrón** (qué regla o tipo de error).
- **Solución** breve (qué hacer en el código).
- **Ejemplo** mínimo (antes/después o fragmento).

Así las próximas conversaciones pueden reutilizar el patrón sin redescubrirlo.

---

## Arreglos recurrentes

*(Actualizar cuando un mismo tipo de fix se aplique 2+ veces.)*

### CodeSniffer

- **Método no CamelCaps** (`Generic.NamingConventions.CamelCapsFunctionName`)  
  - Nombres como `transformarARecordset` o `calcularSACInvestigador` suelen fallar por mayúsculas en medio.  
  - **Solución:** Renombrar a camelCase estricto, p. ej. `transformarToRecordset`, `calcularSacInvestigador` (o `calcularMontosYactualizar`).  
  - Si es método de interfaz, renombrar en la interfaz y en todas las implementaciones y llamadas.

- **Línea > 200 caracteres** (`Generic.Files.LineLength.MaxExceeded`)  
  - **Solución:** Extraer parte de la expresión a una variable o partir la llamada/condición en varias líneas (p. ej. parámetros del método en líneas distintas).

- **Espacio antes de `)`** (`PSR2.Methods.FunctionCallSignature.SpaceBeforeCloseBracket`)  
  - **Solución:** Quitar la coma o el espacio antes del paréntesis de cierre, p. ej. `, )` → `)`.

- **Clase con llave en la misma línea** (`PSR2.Classes.ClassDeclaration.OpenBraceNewLine`)  
  - **Solución:** Poner la `{` en la línea siguiente. Si PHP CS Fixer colapsa de nuevo la clase vacía (`single_line_empty_body`), añadir algo en el cuerpo (p. ej. constructor que llame a `parent::__construct()`).

- **Clase anónima con llave en misma línea** (`PSR12.Classes.AnonClassDeclaration.OpenBraceSameLine`)  
  - CS Fixer puede forzar la llave en la misma línea que `implements`.  
  - **Solución:** Añadir `// phpcs:ignore PSR12.Classes.AnonClassDeclaration.OpenBraceSameLine` en esa línea y partir la declaración (p. ej. `implements` en líneas siguientes) para no superar 200 caracteres.

- **IF vacío** (`Generic.CodeAnalysis.EmptyStatement.DetectedIf`)  
  - **Solución:** Eliminar el `if` y su cuerpo si solo había comentarios; o sustituir por código real si aplica.

### PHPStan

- **`update()` debería retornar `bool` pero retorna `int`**  
  - Eloquent `update()` devuelve `int`.  
  - **Solución:** Cast: `return (bool) $this->model->...->update(...);`

- **Propiedad readonly asignada fuera del constructor**  
  - Si existe un setter (p. ej. `setContext()`), la propiedad no puede ser `readonly`.  
  - **Solución:** Quitar `readonly` de la propiedad en el constructor (promoted property).

- **Parámetro espera `bool`, se pasa `int`**  
  - DTOs con `int` para flags (0/1) y métodos que piden `bool`.  
  - **Solución:** Cast en la llamada: `(bool) $datos->check_lic`, etc.

- **Expresión a la izquierda de `??` no es nullable**  
  - Uso de `??` con constantes o variables que nunca son null.  
  - **Solución:** Quitar el `?? 'fallback'` o inicializar variables antes del `try` si se usan en el `catch`.

- **Variable en `catch` posiblemente no definida**  
  - Variables definidas solo dentro del `try` se usan en el `catch`.  
  - **Solución:** Inicializar esas variables antes del `try` con valores por defecto.

- **`Model::find([...])` retorna Collection, no Model**  
  - Para claves compuestas, `find([$a, $b])` devuelve Collection.  
  - **Solución:** Devolver `$this->model->find([...])->first()` si el método debe retornar `?Model`.

- **Relación no encontrada en modelo (Larastan)**  
  - p. ej. `cargo.empleado` cuando en el modelo la relación se llama `dh01`.  
  - **Solución:** Usar el nombre real de la relación en `whereHas` / `with`, p. ej. `cargo.dh01`.

- **Clase no encontrada (modelo en otro namespace)**  
  - p. ej. `App\Models\Dh19` vs `App\Models\Mapuche\Dh19`.  
  - **Solución:** Corregir el `use` y los type hints al namespace correcto.

- **`query()` en exports FromQuery debe retornar `Builder`**  
  - Laravel Excel: `query()` en clases que implementan `FromQuery` debe tiparse como `\Illuminate\Database\Eloquent\Builder`.  
  - **Solución:** Añadir `use Illuminate\Database\Eloquent\Builder` y declarar `public function query(): Builder`.

- **PhpSpreadsheet `setLocked`/`setHidden` esperan valor de constante**  
  - PHPStan infiere que el parámetro debe ser de tipo int (constantes de `Protection`), no `bool`.  
  - **Solución:** Usar `\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED` en lugar de `false`.

- **`str_pad` segundo parámetro: string**  
  - En PHP 8.4 `str_pad` espera string para el contenido.  
  - **Solución:** Cast: `str_pad((string) $valor, $length, '0')`.

- **Callbacks con tipo no resoluble (uasort, map, sortBy)**  
  - PHPStan no resuelve el tipo del closure en colecciones/arrays.  
  - **Solución:** Añadir `/** @phpstan-ignore-next-line argument.unresolvableType */` encima de la llamada.

### Rector

- **Carbon → Date facade**  
  - Rector puede reemplazar `Carbon::now()` por `Date::now()`. Aceptar o revisar según convención del proyecto.

### Después de editar

- Ejecutar **Pint** sobre archivos tocados: `vendor/bin/pint --dirty --format agent`.
- Volver a ejecutar `composer quality:path` sobre la carpeta para confirmar que todo sigue en verde.

---

## Referencia rápida

- **Script:** `scripts/quality-path.sh`
- **Composer:** `composer quality:path -- <path>`
- **Solo verificación (dry-run):** `composer quality:path:check -- <path>`
- **Pint:** `vendor/bin/pint --dirty --format agent`
- **Doc general de calidad:** `docs/code_quality_workflow.md`

---

*Última actualización: según estado de las carpetas y arreglos recurrentes documentados.*
