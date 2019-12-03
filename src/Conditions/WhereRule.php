<?php

namespace RedFern\ArrayQueryBuilder\Conditions;

use Illuminate\Database\Eloquent\Builder;

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
     * QueryRule constructor.
     *
     * @param $builder
     * @param $rule
     * @param string $condition
     * @param int $index
     */
    public function __construct($builder, $rule, $condition = 'and', $index = 0)
    {
        $this->builder = $builder;
        $this->condition = $condition;
        $this->index = $index;

        $this->fill($rule);
    }

    /**
     * Fill the rule attributes
     *
     * @param $rule
     */
    protected function fill($rule)
    {
        foreach($rule as $key => $value) {
            $this->setAttribute($key, $value);
        }
    }

    /**
     * Apply the query builder changes
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
     * Set an attribute
     *
     * @param $key
     * @param $value
     */
    protected function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Get all attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Get an attribute
     *
     * @param $name
     * @param string $default
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
     * Get an attribute
     *
     * @param $key
     * @return mixed|string
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Set an attribute value
     *
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }
}
