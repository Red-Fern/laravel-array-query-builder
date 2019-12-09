<?php

namespace RedFern\ArrayQueryBuilder\Conditions;

use Illuminate\Support\Collection;

class RelationGroup
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
    protected $condition;

    /**
     * @var string
     */
    protected $parentCondition;

    /**
     * @var string
     */
    protected $relation;

    /**
     * RelationGroup constructor.
     *
     * @param $builder
     * @param $relation
     * @param array|Collection $rules
     * @param string           $condition
     * @param string           $parentCondition
     */
    public function __construct($builder, $rules, $condition = 'and', $parentCondition = '')
    {
        $this->builder = $builder;
        $this->condition = $condition;
        $this->parentCondition = ($parentCondition) ?: $this->condition;
        $this->sanitizeRules($rules);
    }

    /**
     * Build the query.
     *
     * @return mixed
     */
    public function apply()
    {
        $method = ($this->parentCondition == 'or') ? 'orWhereHas' : 'whereHas';

        return $this->builder->{$method}($this->relation, function ($q) {
            $this->applyRules($q);
        });
    }

    /**
     * Apply the query rules.
     *
     * @param $q
     */
    protected function applyRules($q)
    {
        foreach ($this->rules as $index => $rule) {
            (new WhereRule($q, $rule, $this->condition, $index))->apply();
        }
    }

    /**
     * Remove relation prefix from group fields.
     *
     * @param Collection|array $groupRules
     *
     * @return Collection
     */
    public function sanitizeRules($rules)
    {
        $rules = ($rules instanceof Collection) ? $rules : collect($rules);

        $this->relation = preg_replace('/\.([a-z_]*)$/', '', $rules->first()['field']);
        $this->rules = $rules->map(function ($c) {
            $c['field'] = str_replace("{$this->relation}.", '', $c['field']);

            return $c;
        });
    }
}
