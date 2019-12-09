<?php

namespace RedFern\ArrayQueryBuilder\Conditions;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class WhereCollection extends Collection
{
    /**
     * Group the fields together.
     *
     * @return WhereCollection
     */
    public function groupFields()
    {
        return $this->groupBy(function ($item, $index) {
            return $this->groupRelatedFields($item, $index);
        });
    }

    /**
     * Group related fields.
     *
     * @param $item
     * @param int $index
     *
     * @return string|string[]|null
     */
    protected function groupRelatedFields($item, $index = 0)
    {
        if (isset($item['rules'])) {
            return "condition-{$index}";
        }

        if (!isset($item['field'])) {
            return false;
        }

        $key = preg_replace('/\.([a-z_]*)$/', '', $item['field']);

        // If is relation then group by key
        if (Str::contains($item['field'], '.')) {
            return $key;
        }

        // Prevent grouping non relation keys
        return "{$key}-{$index}";
    }

    /**
     * Check if relation group.
     *
     * @return mixed
     */
    public function isRelationalGroup()
    {
        return $this->pipe(function ($collection) {
            $rules = $collection->first(function ($item) {
                return isset($item['rules']);
            });

            return isset($rules['rules']) && collect($rules['rules'])->filter(function ($item) {
                return !isset($item['rules']) && !Str::contains($item['field'], '.');
            })->isEmpty();
        });
    }

    /**
     * Check if contains relational rules.
     *
     * @return bool
     */
    public function isRelational()
    {
        return (bool) $this->filter(function ($item) {
            return isset($item['field']) && Str::contains($item['field'], '.');
        })->first();
    }

    /**
     * Check if data is a group.
     *
     * @return bool
     */
    public function isGroup()
    {
        return (bool) $this->first(function ($item) {
            return isset($item['condition']);
        });
    }
}
