# ConceptosSicossEnum

Clase que maneja los códigos de conceptos para SICOSS (Sistema de Cálculo de Obligaciones de la Seguridad Social).

## Métodos

### Condiciones SQL

#### getSqlConditionAportesSijp()

- Genera la condición SQL para aportes SIJP
- Retorna: string con la condición SQL

#### getSqlConditionAportesInssjp()

- Genera la condición SQL para aportes INSSJP
- Retorna: string con la condición SQL

#### getSqlConditionContribucionesSijp()

- Genera la condición SQL para contribuciones SIJP
- Retorna: string con la condición SQL

#### getSqlConditionContribucionesInssjp()

- Genera la condición SQL para contribuciones INSSJP
- Retorna: string con la condición SQL

## Uso

// Ejemplo de uso para obtener condición SQL de aportes SIJP
$condicion = ConceptosSicossEnum::getSqlConditionAportesSijp();

// Ejemplo de uso para obtener condición SQL de aportes INSSJP
$condicion = ConceptosSicossEnum::getSqlConditionAportesInssjp();

// Ejemplo de uso para obtener condición SQL de contribuciones SIJP
$condicion = ConceptosSicossEnum::getSqlConditionContribucionesSijp();

// Ejemplo de uso para obtener condición SQL de contribuciones INSSJP
$condicion = ConceptosSicossEnum::getSqlConditionContribucionesInssjp();

## Notas

- Todos los métodos son estáticos
- Las condiciones SQL generadas utilizan la columna `codn_conce`
- Los códigos se concatenan usando `implode()` para generar la lista de valores IN
