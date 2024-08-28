<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait que proporciona funcionalidad para trabajar con claves primarias compuestas en modelos Eloquent.
 */
trait HasCompositePrimaryKey
{

    /**
     * Devuelve el nombre de la clave primaria del modelo.
     *
     * @return array Nombres de los campos que componen la clave primaria.
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * Devuelve un array con los valores de los campos que componen la clave primaria del modelo.
     *
     * @return array Valores de los campos que componen la clave primaria.
     */
    public function getKey()
    {
        $attributes = [];
        foreach ($this->getKeyName() as $key) {
            $attributes[$key] = $this->getAttribute($key);
        }
        return $attributes;
    }

    /**
     * Establece los valores de las claves primarias compuestas en la consulta de guardado.
     *
     * Este método recorre los campos que componen la clave primaria del modelo y agrega
     * una condición WHERE para cada uno de ellos en la consulta proporcionada. Esto
     * asegura que la consulta de guardado se aplique únicamente al registro con los
     * valores de clave primaria correspondientes.
     *
     * @param Builder $query La consulta a la que se agregarán las condiciones WHERE.
     * @return Builder La consulta con las condiciones WHERE agregadas.
     */
    protected function setKeysForSaveQuery($query)
    {
        foreach ($this->getKeyName() as $key) {
            $query->where($key, '=', $this->getAttribute($key));
        }
        return $query;
    }

    /**
     * Crea una relación de pertenencia (belongsTo) para un modelo con clave primaria compuesta.
     *
     * Este método crea una nueva instancia de la relación BelongsTo que maneja las restricciones
     * necesarias para trabajar con claves primarias compuestas. Permite definir los campos
     * que componen la clave primaria del modelo relacionado (foreignKeys) y los campos
     * que componen la clave primaria del modelo actual (localKeys).
     *
     * @param string $related Nombre de la clase del modelo relacionado.
     * @param array $foreignKeys Nombres de los campos que componen la clave primaria del modelo relacionado.
     * @param array $localKeys Nombres de los campos que componen la clave primaria del modelo actual.
     * @param string|null $relation Nombre de la relación (opcional).
     * @return BelongsTo Instancia de la relación BelongsTo con las restricciones necesarias.
     */
    public function compositeBelongsTo($related, $foreignKeys, $localKeys, $relation = null)
    {
        if (is_null($relation)) {
            $relation = $this->guessBelongsToRelation();
        }

        $instance = $this->newRelatedInstance($related);

        return new class($instance->newQuery(), $this, $foreignKeys, $localKeys, $relation) extends BelongsTo {
            protected $foreignKeys;
            protected $localKeys;

            /**
             * Construye una nueva instancia de la relación BelongsTo con claves primarias compuestas.
             *
             * Este constructor inicializa los campos `$foreignKeys` y `$localKeys` que se utilizarán
             * para establecer las restricciones necesarias en la consulta de la relación BelongsTo.
             * Además, llama al constructor de la clase padre `BelongsTo` pasando los parámetros
             * correspondientes.
             *
             * @param Builder $query La consulta de la relación BelongsTo.
             * @param Model $child El modelo hijo de la relación BelongsTo.
             * @param array $foreignKeys Los nombres de los campos que componen la clave primaria del modelo relacionado.
             * @param array $localKeys Los nombres de los campos que componen la clave primaria del modelo actual.
             * @param string|null $relation El nombre de la relación (opcional).
             */
            public function __construct(Builder $query, $child, $foreignKeys, $localKeys, $relation)
            {
                $this->foreignKeys = $foreignKeys;
                $this->localKeys = $localKeys;
                parent::__construct($query, $child, null, null, $relation);
            }

            /**
             * Agrega las restricciones necesarias a la consulta de la relación HasMany con clave primaria compuesta.
             *
             * Este método se encarga de agregar las restricciones a la consulta de la relación HasMany
             * para que se consideren los valores de los campos que componen la clave primaria del modelo
             * padre. Utiliza los valores de los campos locales (`$this->localKeys`) para filtrar los
             * registros del modelo relacionado que cumplan con esas condiciones.
             */
            public function addConstraints()
            {
                if (static::$constraints) {
                    $foreignValues = array_map(function ($key) {
                        return $this->child->{$key};
                    }, $this->localKeys);

                    $this->query->whereIn(
                        $this->qualifySubSelectColumn($this->foreignKeys),
                        $this->wrapValuesInArray($foreignValues)
                    );
                }
            }

            /**
             * Obtiene el primer resultado de la consulta.
             *
             * Este método se encarga de ejecutar la consulta almacenada en la propiedad `$query` y devolver
             * el primer resultado obtenido. Es útil cuando se desea obtener un único registro que cumpla
             * con las condiciones de la consulta.
             *
             * @return \Illuminate\Database\Eloquent\Model|null El primer resultado de la consulta, o `null` si no se encontró ningún registro.
             */
            public function getResults()
            {
                return $this->query->first();
            }

            /**
             * Envuelve los valores en un array si hay más de una clave foránea.
             *
             * Este método se encarga de asegurarse de que los valores pasados a la consulta
             * de la relación `HasMany` con clave primaria compuesta se encuentren en un
             * array, independientemente de si hay una o más claves foráneas. Esto es
             * necesario para que la consulta se ejecute correctamente.
             *
             * @param mixed $values Los valores a envolver en un array.
             * @return array Los valores envueltos en un array si es necesario.
             */
            protected function wrapValuesInArray($values)
            {
                return count($this->foreignKeys) > 1 ? [$values] : $values;
            }
        };
    }

    /**
     * Crea una relación HasMany con una clave primaria compuesta.
     *
     * Este método se encarga de crear una relación HasMany entre el modelo actual y un modelo relacionado,
     * donde la clave primaria del modelo relacionado es compuesta. Utiliza los valores de los campos locales
     * (`$this->localKeys`) para filtrar los registros del modelo relacionado que cumplan con esas condiciones.
     *
     * @param string $related El nombre de la clase del modelo relacionado.
     * @param array $foreignKeys Los nombres de los campos que componen la clave primaria del modelo relacionado.
     * @param array $localKeys Los nombres de los campos que componen la clave primaria del modelo actual.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany La relación HasMany con clave primaria compuesta.
     */
    public function compositeHasMany($related, $foreignKeys, $localKeys)
    {
        $instance = $this->newRelatedInstance($related);

        return new class($instance->newQuery(), $this, $foreignKeys, $localKeys) extends HasMany {
            protected $foreignKeys;
            protected $localKeys;

            public function __construct(Builder $query, $parent, $foreignKeys, $localKeys)
            {
                $this->foreignKeys = $foreignKeys;
                $this->localKeys = $localKeys;
                parent::__construct($query, $parent, null, null);
            }

            /**
             * Agrega restricciones a la consulta de la relación HasMany con clave primaria compuesta.
             *
             * Este método se encarga de agregar las restricciones necesarias a la consulta de la relación
             * HasMany, utilizando los valores de los campos locales (`$this->localKeys`) del modelo padre
             * para filtrar los registros del modelo relacionado que cumplan con esas condiciones.
             *
             * @return void
             */
            public function addConstraints()
            {
                if (static::$constraints) {
                    $parentValues = array_map(function ($key) {
                        return $this->parent->{$key};
                    }, $this->localKeys);

                    $this->query->whereIn(
                        $this->qualifySubSelectColumn($this->foreignKeys),
                        $this->wrapValuesInArray($parentValues)
                    );
                }
            }

            /**
             * Envuelve los valores en un array si la cantidad de claves foráneas es mayor a 1.
             * Esto se utiliza para asegurar que la consulta de la relación HasMany con clave primaria compuesta
             * se realice correctamente, independientemente de si la clave primaria tiene una o más columnas.
             *
             * @param array $values Los valores a envolver en un array.
             * @return array Los valores envueltos en un array si es necesario.
             */
            protected function wrapValuesInArray($values)
            {
                return count($this->foreignKeys) > 1 ? [$values] : $values;
            }
        };
    }
}
