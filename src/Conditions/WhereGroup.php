<?php

namespace RedFern\ArrayQueryBuilder\Conditions;

use Illuminate\Support\Str;
use RedFern\ArrayQueryBuilder\Conditions\RelationGroup;
use RedFern\ArrayQueryBuilder\Conditions\WhereCollection;
use RedFern\ArrayQueryBuilder\Conditions\WhereRule;

class WhereGroup
{
    /**
     * @var
     */
    protected $builder;

    /**
     * @var
     */
    protected $rules;

    /**
     * @var string
     */
    protected $parentCondition;

    /**
     * @var mixed
     */
    protected $condition;

    /**
     * QueryGroup constructor.
     *
     * @param $builder
     * @param $rules
     * @param string $parentCondition
     */
    public function __construct($builder, $rules, $parentCondition = '')
    {
        $this->builder = $builder;
        $this->parentCondition = $parentCondition;
        $this->condition = $rules['condition'];
        $this->rules = new WhereCollection($rules['rules']);
    }

    /**
     * Apply the where rules
     *
     * @return mixed
     */
    public function apply()
    {
        $this->rules
            ->groupFields()
            ->values()
            ->each(function($collection, $index) {
                $this->applyRules($collection, $index);
            });

        return $this->builder;
    }

    /**
     * Apply rules
     *
     * @param $collection
     * @param $index
     * @return WhereRule|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|mixed
     */
    protected function applyRules($collection, $index)
    {
        // Check for nested relational groups
        if ($collection->isRelationalGroup()) {
            $group = $collection->first();
            return (new RelationGroup($this->builder, $group['rules'], $group['condition'], $this->condition))->apply();
        }

        // Nested groups
        if ($collection->isGroup()) {
            return $this->applyNestedRules($collection);
        }

        if ($collection->isRelational()) {
            return (new RelationGroup($this->builder, $collection, $this->condition))->apply();
        }

        return (new WhereRule($this->builder, $collection->first(), $this->condition, $index))->apply();
    }

    /**
     * Apply nested conditions
     *
     * @param $collection
     * @return mixed
     */
    protected function applyNestedRules($collection)
    {
        return $this->builder->where(function($q) use($collection) {
            return (new self($q, $collection->first(), $this->condition))->apply();
        }, null, null, $this->condition);
    }
}
