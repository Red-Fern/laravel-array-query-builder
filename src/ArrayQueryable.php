<?php

namespace RedFern\ArrayQueryBuilder;

use RedFern\ArrayQueryBuilder\Conditions\WhereGroup;

trait ArrayQueryable
{
    /**
     * Pass in an array query
     *
     * @param array $rules
     * @return WhereGroup
     */
    public static function arrayWheres(array $rules)
    {
        return (new WhereGroup(static::query(), $rules))->apply();
    }

    /**
     * Apply to existing model object
     *
     * @param array $rules
     * @return mixed
     */
    public function newArrayWheres(array $rules)
    {
        return (new WhereGroup($this->newQuery(), $rules))->apply();
    }
}
