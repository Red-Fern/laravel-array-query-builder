<?php

namespace RedFern\ArrayQueryBuilder\Tests\Unit;

use RedFern\ArrayQueryBuilder\Tests\TestCase;

class QueryBuilderTest extends TestCase
{
    /** @test */
    public function it_adds_array_builder_to_eloquent_builder()
    {
        $rules = [
            'condition' => 'or',
            'rules'     => [
                [
                    'field'    => 'name',
                    'operator' => 'like',
                    'value'    => '%adam%',
                ],
                [
                    'field'    => 'name',
                    'operator' => 'like',
                    'value'    => '%liam%',
                ],
            ],
        ];

        $expected = $this->getEloquentBuilder()
            ->where('name', 'like', '%adam%')
            ->orWhere('name', 'like', '%liam%');

        $builder = $this->getEloquentBuilder()->arrayWheres($rules);

        $this->assertQueryEquals($expected, $builder);
    }

    /** @test */
    public function it_adds_array_builder_to_with_nested_query_to_eloquent_builder()
    {
        $rules = [
            'condition' => 'and',
            'rules'     => [
                [
                    'condition' => 'or',
                    'rules' => [
                        [
                            'field'    => 'name',
                            'operator' => 'like',
                            'value'    => '%adam%',
                        ],
                        [
                            'field'    => 'name',
                            'operator' => 'like',
                            'value'    => '%liam%',
                        ],
                    ],
                ],
                [
                    'field' => 'age',
                    'operator' => '>',
                    'value' => 25,
                ],
            ],
        ];

        $expected = $this->getEloquentBuilder()
            ->where(function ($q) {
                $q->where('name', 'like', '%adam%')
                    ->orWhere('name', 'like', '%liam%');
            })
            ->where('age', '>', 25);

        $builder = $this->getEloquentBuilder()->arrayWheres($rules);

        $this->assertQueryEquals($expected, $builder);
    }
}
