# SIU-Mapuche - Documentación de las operaciones de comunicación AFIP SICOSS

## Formato de salida de archivo generado

En el archivo txt se escriben los datos uno al lado del otro sin delimitarlos. A continuación se muestran datos referentes a los campos.

- **N°:** número de campo relativo.
- **Desde:** marca la posición en el txt en la cual comienza el campo.
- **Long:** la longitud máxima de cada campo, dependiendo del tipo se completará con ceros o espacios en blancos.
- **Tipo:** Puede ser numérico, carácter o decimal (N. C. D respectivamente).
- **Campo:** Nombre de campo, que luego se describe con más detalle abajo del cuadro.
- **Origen:** procedencia del dato.

| Nº  | DESDE | LONG. | TIPO | CAMPO                                           | ORIGEN                          |
|-----|-------|-------|------|-------------------------------------------------|---------------------------------|
| 1   | 1     | 11    | N    | CUIL                                            | Datos Personales de Empleados   |
| 2   | 12    | 40    | C    | Apellido y Nombre                              | Datos Personales de Empleados   |
| 3   | 42    | 1     | N    | Cónyuge                                        | Datos Familiares de Empleados   |
| 4   | 43    | 2     | N    | Cantidad de Hijos                              | Datos Familiares de Empleados   |
| 5   | 45    | 2     | N    | Código de Situación                            | Datos Personales DGI            |
| 6   | 47    | 2     | N    | Código de Condición                            | Datos Personales DGI            |
| 7   | 49    | 3     | N    | Código de Actividad                            | Datos Personales DGI            |
| 8   | 52    | 2     | N    | Código de Zona                                 | Datos Personales DGI            |
| 9   | 54    | 5     | D    | Porcentaje de Aporte Adicional SS              | Datos Personales DGI            |
| 10  | 59    | 3     | N    | Código de Modalidad de Contratación            | Datos Personales DGI            |
| 11  | 62    | 6     | N    | Código de Obra Social                          | Depende de condiciones           |
| 12  | 68    | 2     | N    | Cantidad de Adherentes                         | Depende de condiciones           |
| 13  | 70    | 12    | D    | Remuneración Total                             | Se calcula                      |
| 14  | 82    | 12    | D    | Remuneración Imponible 1                       | Se calcula                      |
| 15  | 94    | 9     | D    | Asignaciones Familiares Pagadas                | Se calcula                      |
| 16  | 103   | 9     | D    | Importe Aporte Voluntario                      | Se calcula                      |
| 17  | 112   | 9     | D    | Importe Adicional OS                           | Se calcula                      |
| 18  | 121   | 9     | D    | Importe Excedentes Aportes SS                  | Por defecto                     |
| 19  | 130   | 9     | D    | Importe Excedentes Aportes OS                  | Por defecto                     |
| 20  | 139   | 50    | C    | Provincia Localidad                            | Datos Personales DGI            |
| 21  | 189   | 12    | D    | Remuneración Imponible 2                       | Se calcula                      |
| 22  | 201   | 12    | D    | Remuneración Imponible 3                       | Se calcula                      |
| 23  | 213   | 12    | D    | Remuneración Imponible 4                       | Se calcula                      |
| 24  | 225   | 2     | N    | Código de Siniestrado                          | Por defecto                     |
| 25  | 227   | 1     | N    | Marca de Corresponde Reducción                 | Por defecto                     |
| 26  | 228   | 9     | D    | Capital de Recomposición de LRT                | Por defecto                     |
| 27  | 237   | 1     | N    | Tipo de empresa                                | Valor en configuración          |
| 28  | 238   | 9     | N    | Aporte Adicional de Obra Social                | Por defecto                     |
| 29  | 247   | 1     | N    | Régimen                                        | Depende de condiciones          |
| 30  | 248   | 2     | N    | Situación de Revista 1                         | Datos Personales DGI            |
| 31  | 250   | 2     | N    | Día inicio Situación de Revista 1              | Por defecto                     |
| 32  | 252   | 2     | N    | Situación de Revista 2                         | Por defecto                     |
| 33  | 254   | 2     | N    | Día inicio Situación de Revista 2              | Por defecto                     |
| 34  | 256   | 2     | N    | Situación de Revista 3                         | Por defecto                     |
| 35  | 258   | 2     | N    | Día inicio Situación de Revista 3              | Por defecto                     |
| 36  | 260   | 12    | D    | Sueldo + Adicionales                           | Se calcula                      |
| 37  | 272   | 12    | D    | SAC                                            | Se calcula                      |
| 38  | 284   | 12    | D    | Horas Extras                                   | Se calcula                      |
| 39  | 296   | 12    | D    | Zona desfavorable                              | Se calcula                      |
| 40  | 308   | 12    | D    | Vacaciones                                     | Se calcula                      |
| 41  | 21    | 9     | D    | Cantidad de días trabajados                    | Por defecto                     |
| 42  | 329   | 12    | D    | Remuneración Imponible 5                       | Se calcula                      |
| 43  | 341   | 1     | N    | Trabajador Convencionado                       | Valor en configuración          |
| 44  | 342   | 12    | N    | Remuneración Imponible 6                       | Se calcula                      |
| 45  | 354   | 1     | N    | Tipo de Operación                              | Se calcula                      |
| 46  | 355   | 12    | N    | Importe Adicionales                            | Se calcula                      |
| 47  | 367   | 12    | N    | Importe Premios                                | Se calcula                      |
| 48  | 379   | 12    | N    | Remuneración / 788/05 – Rem. Imp. 8           | Se calcula                      |
| 49  | 391   | 12    | N    | Remuneración 7                                 | Se calcula                      |
| 50  | 403   | 3     | N    | Cantidad de Horas Extras                       | Se calcula                      |
| 51  | 406   | 12    | N    | Conceptos no remunerativos                      | Se calcula                      |
| 52  | 418   | 12    | N    | Maternidad                                     | Se calcula                      |
| 53  | 430   | 9     | D    | Rectificación de remuneración                   | Se calcula                      |
| 54  | 439   | 12    | D    | Remuneración Imponible 9                       | Se calcula                      |
| 55  | 451   | 9     | D    | Contribución tarea Diferencial                  | Por defecto                     |
| 56  | 460   | 3     | N    | Horas trabajadas                               | Por defecto                     |
| 57  | 463   | 1     | N    | Seguro de Vida Obligatorio                      | Se calcula                      |
| 58  | 464   | 12    | N    | Importe a detraer Ley 27430                    | Se calcula                      |
| 59  | 476   | 12    | N    | Incremento salarial                             | Se calcula                      |
| 60  | 488   | 12    | N    | Remuneración imponible 11                       | Se calcula                      |

## Explicación de campos

A continuación se describen los campos que se informan en el archivo:

1. **CUIL**: Es el valor de CUIP asignado cuando se da de alta el legajo, se encuentra en datos principales de Legajo Electrónico (Actualización / Legajo). Se obtiene de tabla dh01 (Datos Personales de Empleados), campo nro_cuil.

2. **Apellido y Nombre**: Se concatena apellido y nombre del agente. Pueden modificarse en datos principales de legajo bajo las etiquetas apellido y nombre (Actualización / Legajo). Se obtiene de la tabla dh01 (Datos Personales de Empleados), campos: desc_appat y desc_nombr.

3. **Cónyuge**: Un agente tendrá cónyuge si tiene dentro de su grupo familiar un familiar con parentesco igual a cónyuge y además este a cargo. Los familiares pueden verse en la solapa Grupo familiar del legajo (Actualización / Legajo). Se obtiene de la tabla dh02 (Datos Familiares de Empleados) si tiene un familiar cuyo parentesco sea 'CONY' y el familiar este a cargo (sino_cargo igual a 1).

4. **Cantidad de Hijos**: Se informan la cantidad de hijos de un agente. Un agente tendrá hijos si tiene dentro de su grupo familiar un familiar con parentesco igual a hijo y además está a cargo. Se obtiene de la tabla dh02 (Datos Familiares de Empleados) si tiene un familiar cuyo parentesco sea 'HIJO', 'HIJN', 'HINC', 'HINN' y el familiar este a cargo (sino_cargo igual a 1).

5. **Código de Situación**: Este dato se encuentra en datos impositivos del legajo, en solapa códigos y declaraciones juradas. Al agregar un nuevo legajo, se asigna por defecto un valor = 1. Si el agente tiene una licencia con variante del tipo Maternidad en el periodo corriente, aparece un mensaje de aviso con información que el agente tiene Maternidad (Código 5 para la generación de SICOSS de AFIP). Se obtiene de la tabla dha8 (Datos Personales DGI), campo codigosituacion.

6. **Código de Condición**: Este dato se encuentra en datos impositivos del legajo, en solapa códigos y declaraciones juradas. No se puede modificar, lo asigna el sistema. Al agregar un nuevo legajo, se asigna por defecto un valor = 1 si no tiene Estado del Legajo = Jubilado, sino es = 2 para Jubilado. Se obtiene de la tabla dha8 (Datos Personales DGI), campo codigocondicion.

7. **Código de Actividad**: Este dato se encuentra en datos impositivos del legajo, en solapa códigos y declaraciones juradas. Al agregar un nuevo legajo, se asigna por defecto un valor = configuración en Panel de Control. Está en menú Configuración / opción Panel de Control / Seguridad Social y Seguros / Parámetros de Seguridad Social, elemento `Código Actividad AFIP`. Dependiendo de la prioridad se informara el código de actividad del AFIP o no. En el caso de informar código actividad; se obtiene de la tabla dha8 (Datos Personales DGI), campo codigoactividad. Para determinar la prioridad analiza los tipos de grupos de conceptos liquidados.

8. **Código de zona**: Este dato se encuentra en datos impositivos del legajo, en solapa códigos y declaraciones juradas. Al agregar un nuevo legajo, se asigna por defecto un valor = configuración en Panel de Control. Está en menú Configuración / opción Panel de Control / Seguridad Social y Seguros / Parámetros de Seguridad Social, elemento 'Código Zona AFIP'. Se obtiene de la tabla dha8 (Datos Personales DGI), campo codigozona.

9. **Porcentaje de Aporte Adicional SS**: Este dato se encuentra en datos impositivos del legajo, en solapa códigos y declaraciones juradas. Se obtiene de la tabla dha8 (Datos Personales DGI), campo porcaporteadicss.

10. **Código de Modalidad de Contratación**: Este dato se encuentra en datos impositivos del legajo, en solapa códigos y declaraciones juradas. Se obtiene de la tabla dha8 (Datos Personales DGI), campo codigomodalcontrat.

11. **Código de Obra Social**: Es el código de DGI que se corresponde a una obra social asociada a ese legajo. En el caso que sea jubilado directamente el código será = '000000'. Si no es jubilado entonces se fija si el legajo tiene asociada una obra social, esto se encuentra en Legajo / adicionales, si no tiene obra social asignada entonces será la obra social por defecto. La obra social por defecto se encuentra en Configuración / Panel de control / Seguridad Social y Seguros / Parámetros de Seguridad Social. Con el valor obtenido en el anterior paso se busca en la tabla d8 que código DGI se corresponde con el código de obra social, en caso de no existir un código DGI para la obra social se informa '000000'.

12. **Cantidad de Adherentes**: Dependiendo del valor del parámetro Toma en cuenta Familiares a Cargo para informar SICOSS? (O) este valor se obtiene de una forma u otra. Este valor se configura en Menú Configuración / opción Panel de Control / Seguridad Social y Seguros / Impositivos. En caso de que este chequeado, será un Sí; entonces se fija el valor cant_cargo de la tabla dh09 (Otros Datos del Empleado) e informará dicho valor. En caso de que no esté chequeado, será un No; teniendo en cuenta Código Obra social familiar adherente (L), se informa 1 si existe algún concepto liquidado con ese concepto y 0 si no existe ninguno.

13. **Remuneración Total**: Suma el valor de Remuneración Imponible 2 (campo 21) y Conceptos no remunerativos (campo 51).

14. **Remuneración Imponible 1**: Se suman los Tipos de concepto C (campo v). Si Trunca tope jubilatorio (E) esta chequeado se realiza el control de topes para Aportes Jubilatorios y se fija si el SAC (campo 8) supera este tope y resta la diferencia. Se le restan los importes por actividades simultaneas; Importe Bruto Otra Actividad (campo iii) y Importe SAC Otra Actividad (campo iv) y los importes por regímenes especiales; Remuneración Imponible 6 (campo 44).

15. **Asignaciones Familiares Pagadas**: Se realiza una sumatoria de los importes de cada concepto liquidado de tipo F que figure en la lista de conceptos liquidados; Tipos de concepto F (campo vi). Hay que tener en cuenta que se permite acumular la Asignación Familiar en Remuneración Total (importe Bruto no imponible), mediante Acumular Asignación Familiar (F) en RRHHINI. Si esta chequeado, se acumula el valor de Asignaciones Familiares a Remuneración Total y la Asignación Familiar queda en CERO.

16. **Importe Aporte Voluntario**: Suma los importes liquidados de conceptos iguales a el concepto definido como Aportes Voluntarios (M).

17. **Importe Adicional OS**: Suma los importes liquidados de conceptos iguales a el concepto definido como Código Obra social familiar adherente (L).

18. **Importe Excedentes Aportes SS**: Por defecto se informa 0.0.

19. **Importe Excedentes Aportes OS**: Por defecto se informa 0.0.

20. **Provincia Localidad**: Está en menú Configuración / opción Panel de Control / Seguridad Social y Seguros / Parámetros de Seguridad Social, elemento Pcia. Localidad DGI. Se obtiene de la tabla dha8 (Datos Personales DGI), campo !ProvinciaLocalidad.

21. **Remuneración Imponible 2**: Se inicia igual que el importe imponible 1 (campo 14) y trunca según el tope colocado si esta chequeado el parámetro Trunca tope jubilatorio (E).

22. **Remuneración Imponible 3**: Se informa el mismo valor que Remuneración Imponible 2 (campo 21).

23. **Remuneración Imponible 4**: Se suman los Tipos de concepto C (campo v). Si Trunca tope jubilatorio (E) esta chequeado se realiza el control de topes para Otros Aportes (Obra Social y Fondo Solidario de Redistribución) y se fija si el SAC (campo 8) supera este tope y resta la diferencia.

24. **Código de Siniestrado**: Por defecto se informa: 00.

25. **Marca de Corresponde Reducción**: Por defecto se informa: 0.

26. **Capital de Recomposición de LRT**: Por defecto se informa: 0.0.

27. **Tipo de empresa**: Informa Tipo Empresa (K) de RRHHINI.

28. **Aporte Adicional de Obra Social**: Por defecto se informa 0.0.

29. **Régimen**: Informa 1 o 0. Informa 1 si el campo codc_bprev de la tabla dh09 (Otros Datos del Empleado) es igual al dato de RRHHINI Cod. Regimen de Reparto (1), caso contrario informa 0.

30. **Situación de Revista 1**: Se informa el mismo valor que Código de Situación (campo 5).

31. **Día inicio Situación de Revista 1**: Por defecto se informa: 1.

32. **Situación de Revista 2**: Por defecto se informa: 00.

33. **Día inicio Situación de Revista 2**: Por defecto se informa: 00.

34. **Situación de Revista 3**: Por defecto se informa: 00.

35. **Día inicio Situación de Revista 3**: Por defecto se informa: 00.

36. **Sueldo + Adicionales**: Se calcula en base a otros campos.

37. **SAC**: Suma los importes liquidados de conceptos en los cuales en su conjunto de tipos de grupos está incluido el 9.

38. **Horas Extras**: Suma los importes liquidados de conceptos en los cuales en su conjunto de tipos de grupos está incluido el 6. Nota: Tener en cuenta Configuración > Impositivos > Parámetros > Cantidad de Horas Extras SiCOSS según Novedades.

39. **Zona desfavorable**: Suma los importes liquidados de conceptos en los cuales en su conjunto de tipos de grupos está incluido el 7.

40. **Vacaciones**: Suma los importes liquidados de conceptos en los cuales en su conjunto de tipos de grupos está incluido el 8.

41. **Cantidad de días trabajados**: Por defecto se informa: 000000033 (se completa de ceros la longitud que debe tomar el campo).

42. **Remuneración Imponible 5**: Se suman los Tipos de concepto C (campo v). Si Trunca tope jubilatorio (E) está chequeado se realiza el control de topes para otros aportes (PAMI y LRT) y se fija si SAC (campo 8) supera este tope y resta la diferencia.

43. **Trabajador Convencionado**: Informa Trabajador Convencionado (G) de tabla RRHHINI.

44. **Remuneración Imponible 6**: Su valor es la suma de los importes liquidados de conceptos en los cuales en su conjunto de tipos de grupos está incluido alguno de los siguientes: 11, 12, 15 o 49. Se le aplica el Porcentaje Aporte Adicional Jubilación (N) y además se redondea con respecto a Remuneración Imponible 1 (campo 14).

45. **Tipo de Operación**: Toma el valor 1 ó 2 según si se redondea en cálculo de Remuneración Imponible 6 (campo 44).

46. **Importe Adicionales**: Suma los importes liquidados de conceptos en los cuales en su conjunto de tipos de grupos está incluido el 21.

47. **Importe Premios**: Suma los importes liquidados de conceptos en los cuales en su conjunto de tipos de grupos está incluido el 22.

48. **Remuneración / 788/05 – Imponible 8**: Informa el valor de la Remuneración 4 (campo 23) sin tope. Contiene el Importe Imponible, o sea; Tipos de concepto C (campo v). Para el caso que no se trunque con el tope jubilatorio patronal el valor de la remuneración 8 sería el mismo que el de la Remuneración Imponible 2 (campo 21).

49. **Remuneración 7**: Se informa el mismo valor que Remuneración Imponible 6 (campo 44).

50. **Cantidad de Horas Extras**: Suma los importes liquidados de conceptos en los cuales en su conjunto de tipos de grupos está incluido el 4. Nota: Tener en cuenta Configuración > Impositivos > Parámetros > Cantidad de Horas Extras SiCOSS según Novedades.

51. **Conceptos no remunerativos**: Suma los importes liquidados de conceptos en los cuales en su conjunto de tipos de grupos está incluido el 45.

52. **Maternidad**: Suma los importes liquidados de conceptos en los cuales en su conjunto de tipos de grupos está incluido el 47.

53. **Rectificación de remuneración**: Suma los importes liquidados de conceptos en los cuales en su conjunto de tipos de grupos está incluido el 46.

54. **Remuneración Imponible 9**: Se informa el valor de la suma del valor de la remuneración 8 (788/05) + Conceptos No remunerativos (consignados en el campo nro 51). Válido desde SIU-Mapuche 1.21.0.

55. **Contribución tarea diferencial**: Por defecto se informa: 0.0.

56. **Horas trabajadas**: Por defecto se informa: 000.

57. **Seguro de Vida Obligatorio**: Se recorren los conceptos liquidados; si algún tipo de grupo de algún concepto liquidado es igual a 58 entonces se informa 1, sino 0.

## Otros valores

Estos valores no se informan pero intervienen en los cálculos de algunos valores de los campos a informar:

- **Cargo Investigador**: Es verdadero si alguno de los conceptos liquidados pertenece a alguno de los tipos de grupo: 49, 15, 14, 12, 13, 11, sino es falso.
- **SAC Investigador**: Se acumula Importe SAC (campo 8) para cada concepto liquidado en el cual Cargo Investigador (campo i) sea verdadero.
- **Importe Bruto Otra Actividad**: Se obtiene de la tabla dhe9 (Datos de Actividad Simultanea del Empleado) se busca el registro más actual y se toma el valor del campo importe. En caso de no haber registros se toma como valor cero.
- **Importe SAC Otra Actividad**: Se obtiene de la tabla dhe9 (Datos de Actividad Simultanea del Empleado) se busca el registro más actual y se toma el valor del campo importe_sac. En caso de no haber registros se toma como valor cero.
- **Tipos de concepto C**: Se realiza una sumatoria de los importes de cada concepto liquidado de tipo_conce = C que figure en la lista de conceptos liquidados y que cumpla con: nro_orimp > 0 y codn_conce > 0.
- **Tipos de concepto F**: Se realiza una sumatoria de los importes de cada concepto liquidado de tipo_conce = F que figure en la lista de conceptos liquidados.
