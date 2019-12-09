<?php

namespace RedFern\ArrayQueryBuilder\Conditions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use RedFern\ArrayQueryBuilder\Exceptions\InvalidOperatorException;

class WhereRule
{
    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var string
     */
    protected $condition = 'and';

    /**
     * @var int
     */
    protected $index;

    /**
     * @var \Illuminate\Config\Repository
     */
    protected $aliases = [];

    /**
     * @var array
     */
    protected $acceptedOperators = [
        '=',
        '!=',
        '>',
        '>=',
        '<',
        '<=',
        'like',
        'not like',
        'null',
        'not null',
        'between',
        'in',
        'not in',
        'contains',
        'has',
        'doesnt have'
    ];

    /**
     * QueryRule constructor.
     *
     * @param $builder
     * @param $rule
     * @param string $condition
     * @param int    $index
     */
    public function __construct($builder, $rule, $condition = 'and', $index = 0)
    {
        $this->builder = $builder;
        $this->condition = $condition;
        $this->index = $index;
        $this->aliases = config('laravelarrayquerybuilder.operator_aliases', []);

        $this->fill($rule);
    }

    /**
     * Fill the rule attributes.
     *
     * @param $rule
     */
    protected function fill($rule)
    {
        foreach ($rule as $key => $value) {
            $this->setAttribute($key, $value);
        }
    }

    /**
     * Apply the query builder changes.
     */
    public function apply()
    {
        $condition = ($this->index) ? $this->condition : 'and';

        // Null value check
        if ($this->operator == 'null') {
            return $this->builder->whereNull($this->field, $condition);
        }

        // Add not null check
        if ($this->operator == 'not null') {
            return $this->builder->whereNotNull($this->field, $condition);
        }

        if ($this->operator == 'between') {
            return $this->builder->whereBetween($this->field, $this->value, $condition);
        }

        if ($this->operator == 'in') {
            return $this->builder->whereIn($this->field, $this->value, $condition);
        }

        if ($this->operator == 'not in') {
            return $this->builder->whereNotIn($this->field, $this->value, $condition);
        }

        if ($this->operator == 'contains') {
            return $this->builder->where($this->field, 'like', "%{$this->value}%", $condition);
        }

        if ($this->operator == 'has') {
            return $this->builder->has($this->field);
        }

        if ($this->operator == 'doesnt have') {
            return $this->builder->doesntHave($this->field);
        }

        return $this->builder->where($this->field, $this->operator, $this->value, $condition);
    }

    /**
     * Set the operator
     *
     * @return mixed|string
     * @throws InvalidOperatorException
     */
    protected function setOperatorValue($value)
    {
        if (array_key_exists($value, $this->aliases)) {
            $value = $this->aliases[$value];
        }

        if (!$this->validOperator($value)) {
            throw new InvalidOperatorException("The operator \"{$value}\" is not valid");
        }

        return $value;
    }

    /**
     * Check if operator is in whitelisted acceptedOperators
     *
     * @param $operator
     * @return bool
     */
    protected function validOperator($operator)
    {
        return in_array($operator, $this->acceptedOperators);
    }

    /**
     * Set an attribute.
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function setAttribute($key, $value)
    {
        // Check if a mutator method for given key exists
        $method = $this->getMutatorMethod($key);
        if (method_exists($this, $method)) {
            $this->attributes[$key] = $this->$method($value);
            return $this;
        }

        $this->attributes[$key] = $value;
    }

    /**
     * Get all attributes.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Get a custom mutator method name
     *
     * @param $key
     * @return string
     */
    public function getMutatorMethod($key)
    {
        $attribute = ucfirst(Str::camel($key));

        return "set{$attribute}Value";
    }

    /**
     * Get an attribute.
     *
     * @param $name
     * @param string $default
     *
     * @return mixed|string
     */
    public function getAttribute($name, $default = '')
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        return $default;
    }

    /**
     * Get an attribute.
     *
     * @param $key
     *
     * @return mixed|string
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Set an attribute value.
     *
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }
}
