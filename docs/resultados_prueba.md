# resultados

## paso 1

.- Precarga conceptos de todos los legajos -- ok
.- agrupa conceptos por legajo - ok

## paso 2

.- Precarga todos los cargos -- ok

## paso 3

.- precargar otra actividad todos los legajos -- ok

## paso 4

.- precarga codigo obra social -- es siempre '000000'

## paso 5

### bucle for

#### $legajoActual['codigo_os']

array:1 [
  110830 => "000000"
]

#### limites

array:2 [
  "minimo" => 1
  "maximo" => 30
]

```json
-> CARGOS ACTIVOS SIN LICENCIA {
  "legajo":110830,
  "cargos_activos_sin_licencia":[{
    "nro_cargo":34135,
    "inicio":1,
    "final":30}],
    "limites":{"minimo":1,"maximo":30}}
```

### limites nuevos

* Limites nuevos: {"minimo":1,"maximo":30}

### estado de situacion

```php
$Estado situacion: [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1]
```

## cambios de estado

```php
Cambios estado:  [1] 
Dias trabajados:  [30] 
Revista legajo:  [{"codigo":1,"dia":1},{"codigo":0,"dia":0},{"codigo":0,"dia":0}] 
```

## LegajoActual

```json
Legajo actual:  {
  "nro_legaj":110830,
  "cuit":"20183035860",
  "apyno":"CES ROMERO RAMON",
  "estado":"A",
  "conyugue":0,
  "hijos":0,
  "provincialocalidad":null,
  "codigosituacion":1,
  "codigocondicion":1,
  "codigozona":"1",
  "codigoactividad":17,
  "aporteadicional":null,
  "trabajadorconvencionado":null,
  "codigocontratacion":8,
  "regimen":"1",
  "adherentes":0,
  "licencia":0,
  "importeimponible_9":0,
  "ImporteSACOtroAporte":0,
  "TipoDeOperacion":0,
  "ImporteImponible_4":0,
  "ImporteSACNoDocente":0,
  "ImporteSACDoce":0,
  "ImporteSACAuto":0,
  "codigo_os":"000000",
  "codigorevista1":1,
  "fecharevista1":1,
  "codigorevista2":1,
  "fecharevista2":0,
  "codigorevista3":1,
  "fecharevista3":0,
  "dias_trabajados":30
  } 
```

## Conceptos_por_legajo

```php
Conceptos legajo:
...
```

```php
    $legajoActual['Remuner78805']               = $suma_conceptos_tipoC;
    $legajoActual['AsignacionesFliaresPagadas'] = $suma_conceptos_tipoF;
    $legajoActual['ImporteImponiblePatronal']   = $suma_conceptos_tipoC;

Remuner 78805:  [5382827.51] 
Asignaciones Familiares:  [0.0] 
Importe Imponible Patronal:  [5382827.51] 
```

## calcular remuneracion total = IMPORTE_BRUTO

* ImporteSACPatronal = ImporteSAC
* ImporteImponibleSinSAC = ImporteImponiblePatronal - ImporteSACPatronal

```php
# En el legajo 110830 se cumple la condicion
# ✅ Trunca el tope de SAC
if ($legajoActual['ImporteSAC'] > $TopeSACJubilatorioPatr  && $trunca_tope == 1) {
                $legajoActual['DiferenciaSACImponibleConTope']  = $legajoActual['ImporteSAC'] - $TopeSACJubilatorioPatr;
                $legajoActual['ImporteImponiblePatronal']      -= $legajoActual['DiferenciaSACImponibleConTope'];
                $legajoActual['ImporteSACPatronal']             = $TopeSACJubilatorioPatr;
            }

"ImporteSAC" => 2242844.8
"ImporteImponiblePatronal" => 3564542.71
"DiferenciaSACImponibleConTope" => 1818284.8
"DiferenciaImponibleConTope" => 0
"ImporteSACPatronal" => 424560.0
"ImporteImponibleSinSAC" => 3139982.71

```

### trunca el tope imponible

```php
Este tambien se ejecuta con el legajo 110830
✅ Trunca el tope de Imponible {"ImporteImponibleSinSAC":3139982.71,"TopeJubilatorioPatronal":849120.0}

if ($legajoActual['ImporteImponibleSinSAC'] > $TopeJubilatorioPatronal && $trunca_tope == 1) {
                Log::debug('✅ Trunca el tope de Imponible',[
                    'ImporteImponibleSinSAC' => $legajos[$i]['ImporteImponibleSinSAC'],
                    'TopeJubilatorioPatronal' => $TopeJubilatorioPatronal,
                ]);
                $legajoActual['DiferenciaImponibleConTope'] = $legajos[$i]['ImporteImponibleSinSAC'] - $TopeJubilatorioPatronal;
                $legajoActual['ImporteImponiblePatronal']  -= $legajos[$i]['DiferenciaImponibleConTope'];
            }
```

### IMPORTE_BRUTO

```php
$legajoActual['IMPORTE_BRUTO'] = $legajoActual['ImporteImponiblePatronal'] + $legajoActual['ImporteNoRemun']
```

### calcular IMPORTE_IMPON que es lo mismo que importe imponible 1

```php
$legajoActual['IMPORTE_IMPON'] = $suma_conceptos_tipoC;
```

### check becarios

```php
if ($legajoActual['ImporteImponibleBecario'] != 0) # en el legjo 110830 no se cumple
```

### importes imponibles, otro check

```php
Importes Imponibles {
  "PorcAporteDiferencialJubilacion":2.0,
  "ImporteImponible_4":5382827.51,
  "ImporteSACNoDocente":0,
  "ImporteImponible_6":0,
  "Imponible6_aux":0.0
  } 
```

### Imponible6_aux

```php
{
  "TipoDeOperacion":1,
  "ImporteSACNoDocente":2242844.8
  } 
```

### checkpoint

```php
array:70 [
  "nro_legaj" => 110830
  "cuit" => "20183035860"
  "apyno" => "CES ROMERO RAMON"
  "estado" => "A"
  "conyugue" => 0
  "hijos" => 0
  "provincialocalidad" => null
  "codigosituacion" => 1
  "codigocondicion" => 1
  "codigozona" => "1"
  "codigoactividad" => 17
  "aporteadicional" => null
  "trabajadorconvencionado" => null
  "codigocontratacion" => 8
  "regimen" => "1"
  "adherentes" => 0
  "licencia" => 0
  "importeimponible_9" => 0
  "ImporteSACOtroAporte" => 2242844.8
  "TipoDeOperacion" => 1
  "ImporteImponible_4" => 5382827.51
  "ImporteSACNoDocente" => 2242844.8
  "ImporteSACDoce" => 0
  "ImporteSACAuto" => 0
  "codigo_os" => "000000"
  "codigorevista1" => 1
  "fecharevista1" => 1
  "codigorevista2" => 1
  "fecharevista2" => 0
  "codigorevista3" => 1
  "fecharevista3" => 0
  "dias_trabajados" => 30
  "ImporteSAC" => 2242844.8
  "SACPorCargo" => 0
  "ImporteHorasExtras" => 0
  "ImporteVacaciones" => 0
  "ImporteRectificacionRemun" => 0
  "ImporteAdicionales" => 0
  "ImportePremios" => 0
  "ImporteNoRemun" => 410
  "ImporteMaternidad" => 0
  "ImporteZonaDesfavorable" => 0
  "PrioridadTipoDeActividad" => 0
  "IMPORTE_VOLUN" => 0
  "IMPORTE_ADICI" => 0
  "TipoDeActividad" => 17
  "ImporteImponible_6" => 0
  "SACInvestigador" => 0
  "CantidadHorasExtras" => 0
  "SeguroVidaObligatorio" => 1
  "ImporteImponibleBecario" => 0
  "AporteAdicionalObraSocial" => 0
  "ImporteSICOSS27430" => 0
  "ImporteSICOSSDec56119" => 0
  "ImporteSACNodo" => 2242844.8
  "ContribTareaDif" => 0
  "NoRemun4y8" => 0
  "IncrementoSolidario" => 0
  "ImporteNoRemun96" => 0
  "ImporteTipo91" => 0
  "Remuner78805" => 5382827.51
  "AsignacionesFliaresPagadas" => 0.0
  "ImporteImponiblePatronal" => 1273680.0
  "DiferenciaSACImponibleConTope" => 0
  "DiferenciaImponibleConTope" => 0
  "ImporteSACPatronal" => 424560.0
  "ImporteImponibleSinSAC" => 3139982.71
  "IMPORTE_BRUTO" => 1274090.0
  "IMPORTE_IMPON" => 5382827.51
  "PorcAporteDiferencialJubilacion" => 2.0
] 
```

### tope jubilatorio personal

```php
{"tope_jubil_personal":1273680.0}
```

### ImporteSacNoDocente es mayor al tope

```php
{
  "tope_jubil_personal":1273680.0,
  "DiferenciaSACImponibleConTope":1818284.7999999998,
  "IMPORTE_IMPON":3564542.71,
  "ImporteSACNoDocente":424560.0} 
```

### categoria diferencial

```php
  "DECP','DECE','DECC','VIDC','VIDP','VIDE','VIRC','VIRP','VIRE"
```

### nuevo ImporteImponibleSinSAC

```php
  ✅ Importe Imponible sin SAC {"ImporteImponibleSinSAC":3139982.71} 
```

### ImporteImponibleSinSAC es mayor al tope

```php
  {"tope_jubil_personal":1273680.0,
  "DiferenciaImponibleConTope":2290862.71,
  "IMPORTE_IMPON":1273680.0,
  "ImporteImponibleSinSAC":3139982.71} 
```

### Importe Bruto y SAC de otra actividad

```php
  {
    "ImporteBrutoOtraActividad":0,
    "ImporteSACOtraActividad":0
  } 
```

### ImporteSACOtroAporte es mayor al tope

```php
['DifSACImponibleConOtroTope'] = ['ImporteSACOtroAporte'] - $TopeSACJubilatorioOtroAp;
['ImporteImponible_4']        -= ['DifSACImponibleConOtroTope'];
['ImporteSACOtroAporte']       = $TopeSACJubilatorioOtroAp;

  {"TopeSACJubilatorioOtroAp":424560.0,
  "DifSACImponibleConOtroTope":1818284.7999999998,
  "ImporteImponible_4":3564542.71,
  "ImporteSACOtroAporte":424560.0} 
```

### OtroImporteImponibleSinSAC es mayor al tope

```php
  {"TopeOtrosAportesPersonales":849120.0,
  "DifImponibleConOtroTope":2290862.71,
  "ImporteImponible_4":1273680.0,
  "OtroImporteImponibleSinSAC":3139982.71} 
```

### SueldoMasAdicionales

```php
ImporteSueldoMasAdicionales {"ImporteSueldoMasAdicionales":-969164.7999999998}
```

### AsignacionFamiliar

modifica IMPORTE_BRUTO si hay asignacion familiar

### ImporteImponible_9

```php
importeImponible_9 {"impoteimponible_9":5382827.51} 
if (MapucheConfig::getParametroRrhh('Sicoss', 'ConceptosNoRemuEnART', '0') === '1') 
    importeImponible_9 + ImporteNoRemun {"impoteimponible_9":5383237.51} 
```

### GDS #5913

```php
  {"Remuner78805":5382827.51,
  "ImporteImponible_4":1273680.0,
  "ImporteImponible_5":1273680.0} 
```

## Totales

```php
  "bruto":1274090.0,
  "imponible_1":1273680.0,
  "imponible_2":1273680.0,
  "imponible_4":1273680.0,
  "imponible_5":1273680.0,
  "imponible_8":5382827.51,
  "imponible_6":0.0,
  "imponible_9":5383237.51
```

## RESULTADO

```php
   - check_retro: 0
   - nro_legaj: 110830
   - check_lic:
   - check_sin_activo:
   - truncaTope: 1
   - TopeJubilatorioPatronal: 849120
   - TopeJubilatorioPersonal: 849120
   - TopeOtrosAportesPersonal: 849120
   - periodo_fiscal: 202506
array:1 [
  0 => array:77 [
    "nro_legaj" => 110830
    "cuit" => "20183035860"
    "apyno" => "CES ROMERO RAMON"
    "estado" => "A"
    "conyugue" => 0
    "hijos" => 0
    "provincialocalidad" => null
    "codigosituacion" => 1
    "codigocondicion" => 1
    "codigozona" => "1"
    "codigoactividad" => 17
    "aporteadicional" => null
    "trabajadorconvencionado" => "0"
    "codigocontratacion" => 8
    "regimen" => "1"
    "adherentes" => 0
    "licencia" => 0
    "importeimponible_9" => 5383237.51
    "ImporteSACOtroAporte" => 424560.0
    "TipoDeOperacion" => 1
    "ImporteImponible_4" => 1273680.0
    "ImporteSACNoDocente" => 424560.0
    "ImporteSACDoce" => 0
    "ImporteSACAuto" => 0
    "codigo_os" => "000000"
    "codigorevista1" => 1
    "fecharevista1" => 1
    "codigorevista2" => 1
    "fecharevista2" => 0
    "codigorevista3" => 1
    "fecharevista3" => 0
    "dias_trabajados" => 30
    "ImporteSAC" => 2242844.8
    "SACPorCargo" => 0
    "ImporteHorasExtras" => 0
    "ImporteVacaciones" => 0
    "ImporteRectificacionRemun" => 0
    "ImporteAdicionales" => 0
    "ImportePremios" => 0
    "ImporteNoRemun" => 410
    "ImporteMaternidad" => 0
    "ImporteZonaDesfavorable" => 0
    "PrioridadTipoDeActividad" => 0
    "IMPORTE_VOLUN" => 0
    "IMPORTE_ADICI" => 0
    "TipoDeActividad" => 17
    "ImporteImponible_6" => 0
    "SACInvestigador" => 0
    "CantidadHorasExtras" => 0
    "SeguroVidaObligatorio" => 1
    "ImporteImponibleBecario" => 0
    "AporteAdicionalObraSocial" => 0
    "ImporteSICOSS27430" => 0
    "ImporteSICOSSDec56119" => 0
    "ImporteSACNodo" => 2242844.8
    "ContribTareaDif" => 0
    "NoRemun4y8" => 0
    "IncrementoSolidario" => 0
    "ImporteNoRemun96" => 0
    "ImporteTipo91" => 0
    "Remuner78805" => 5382827.51
    "AsignacionesFliaresPagadas" => 0.0
    "ImporteImponiblePatronal" => 1273680.0
    "DiferenciaSACImponibleConTope" => 1818284.8
    "DiferenciaImponibleConTope" => 2290862.71
    "ImporteSACPatronal" => 424560.0
    "ImporteImponibleSinSAC" => 3139982.71
    "IMPORTE_BRUTO" => 1274090.0
    "IMPORTE_IMPON" => 1273680.0
    "PorcAporteDiferencialJubilacion" => 2.0
    "ImporteBrutoOtraActividad" => 0
    "ImporteSACOtraActividad" => 0
    "DifSACImponibleConOtroTope" => 1818284.8
    "DifImponibleConOtroTope" => 2290862.71
    "OtroImporteImponibleSinSAC" => 3139982.71
    "ImporteSueldoMasAdicionales" => -969164.8
    "ImporteImponible_5" => 1273680.0
  ]
] 
```
