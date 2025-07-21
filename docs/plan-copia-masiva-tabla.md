# Plan de Implementación: Copia Masiva de Registros entre Bases de Datos con Tracking de Progreso

## 1. Objetivo

Permitir a un usuario administrativo copiar registros de una tabla específica desde la base de datos `pgsql-prod` a `pgsql-consulta`, filtrando por una liquidación seleccionada, con seguimiento de progreso y notificación al finalizar.

---

## 2. Componentes Principales

### a) **Panel Administrativo Filament**

- Página donde el usuario selecciona la liquidación y lanza la copia.
- Visualización del progreso (barra, porcentaje, estado).
- Notificación al finalizar (éxito o error).

### b) **Job Asíncrono (Queue)**

- Procesa la copia en background usando chunking para eficiencia.
- Actualiza el progreso en una tabla de tracking.
- Maneja errores y reintentos.

### c) **Tracking del Progreso**

- Tabla `copy_jobs` para registrar cada operación:
- Usuario, tabla origen/destino, liquidación, total y copiados, estado, timestamps, errores.
- El Job actualiza el progreso periódicamente.
- El panel consulta y muestra el avance en tiempo real.

### d) **Notificaciones**

- Al finalizar, el usuario recibe una notificación (Filament Notification, email, etc.).

---

## 3. Flujo de Trabajo

1. **El usuario accede al panel Filament y selecciona la liquidación.**
2. **Al lanzar la copia:**

   - Se crea un registro en `copy_jobs` con estado `pending`.
   - Se despacha un Job asíncrono.

3. **El Job:**
   - Calcula el total de registros a copiar.
   - Procesa en chunks, copiando de `pgsql-prod` a `pgsql-consulta`.
   - Actualiza el campo `copied_records` y el estado en cada chunk.
   - Si termina, marca como `completed` y registra la hora.
   - Si falla, marca como `failed` y guarda el error.

4. **El panel Filament consulta periódicamente el estado y muestra el progreso.**

5. **Al finalizar, el usuario es notificado.**

---

## 4. Consideraciones Técnicas

- Usar chunking para evitar memory leaks.
- Manejar transacciones y errores.
- Permitir reintentos y logs detallados.
- Optimizar consultas para grandes volúmenes.
- Proteger la operación con permisos adecuados.

---

## 5. Estructura de la Tabla de Tracking (`copy_jobs`)

```bash
| Campo            | Tipo      | Descripción                                |
|------------------|-----------|--------------------------------------------|
| id               | bigint    | ID autoincremental                         |
| user_id          | bigint    | Usuario que lanzó la copia                 |
| source_table     | string    | Tabla origen                               |
| target_table     | string    | Tabla destino                              |
| liquidation_id   | bigint    | ID de la liquidación                       |
| total_records    | int       | Total de registros a copiar                |
| copied_records   | int       | Registros copiados hasta el momento        |
| status           | string    | Estado: pending, running, completed, failed|
| started_at       | timestamp | Inicio del proceso                         |
| finished_at      | timestamp | Fin del proceso                            |
| error_message    | text      | Mensaje de error si falló                  |
| created_at       | timestamp | Creación                                   |
| updated_at       | timestamp | Actualización                              |


````

---

## 6. Próximos Pasos

1. Crear migración y modelo Eloquent para `copy_jobs`.
2. Implementar Job de copia masiva con chunking y actualización de progreso.
3. Crear página Filament/Livewire para lanzar el proceso y mostrar el progreso.
4. Implementar notificaciones al usuario.
