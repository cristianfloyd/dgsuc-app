# Tablas finales (persistentes, afectadas permanentemente)

* mapuche.dp19 (Unidad Principal)
* mapuche.dp18 (Subunidad Principal)
* mapuche.subsubde (Sub Sub Unidad)
* mapuche.dh27 (Red Programática)
* mapuche.ejercicio_financiero
* mapuche.gp_presu (Grupos Presupuestarios)
* mapuche.presupb (Imputación Presupuestaria)

## Relaciones lógicas

* dp19 (área) se relaciona con dp18 (subárea) por codn_area
* dp18 (subárea) se relaciona con subsubde (subsubárea) por codn_area y codn_subar
* dh27 contiene referencias a programas, subprogramas, proyectos, actividades y obras (estructura programática)
* presupb contiene referencias a:
  * área (coddep)
  * subárea (subdep)
  * subsubárea (codn_subsubar)
  * grupo presupuestario (codn_grupo_presup)
  * ejercicio financiero (tipo_ejercicio)
  * red programática (clase02 = programa+subprograma+proyecto+actividad+obra)
  * objeto del gasto (clase03)
  * fuente de financiamiento (clase01)

## Tablas consultadas

* mapuche.dh21
* mapuche.dh22
* mapuche.dh91
* mapuche.dh03
* mapuche.dh35
* mapuche.dh17
* mapuche.dh45
* mapuche.dh46
* mapuche.dh89
* mapuche.imp_liquidaciones
* mapuche.imp_partida_liquidacion
* mapuche.imp_partidas

## Referencias

- Ref: dp19.codn_area = dp18.codn_area
- Ref: dp18.codn_area = subsubde.codn_area
- Ref: dp18.codn_subar = subsubde.codn_subar
- Ref: dp19.codn_area = presupb.coddep
- Ref: dp18.codn_subar = presupb.subdep
- Ref: subsubde.codn_subsubar = presupb.codn_subsubar
- Ref: gp_presu.grupo = presupb.codn_grupo_presup
- Ref: ejercicio_financiero.ejercicio = presupb.tipo_ejercicio