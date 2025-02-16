# Controles Sicoss 

## 1. Controles principales

(El trait SicossConnectionTrait establece la conexion de la based de datos)
1. Control de Aportes y Contribuciones
    - Compara los aportes y contribuciones calculados en DH21 vs los registrados en SICOSS
    - Incluye:
        - Aportes SIJP (códigos 201, 202, 203, 205, 204)
        - Aportes INSSJP (código 247)
        - Contribuciones SIJP (códigos 301, 302, 303, 304, 307)
        - Contribuciones INSSJP (código 347)
    - Verifica diferencias por CUIL
    - Agrupa diferencias por dependencia y carácter
2. Control de ART
    - Compara base imponible ART (códigos 100-199, excepto 198)
    - Verifica contribuciones ART (códigos 306, 308)
    - Calcula diferencias por CUIL
    - Incluye control de la fórmula de cálculo ((rem_imp9 0.005) + 1172)
3. Control de CUILs
    - Verifica CUILs existentes en DH21 pero no en SICOSS
    - Verifica CUILs existentes en SICOSS pero no en DH21
4.- Control de UA/CAD y Carácter
    - Verifica y actualiza dependencia (UA/CAD)
    - Verifica y actualiza carácter (PERM/CONT)

## 2. Tablas temporales utilizadas

- dh21aporte: Almacena totales de aportes y contribuciones
- dh21art: Almacena totales de ART
