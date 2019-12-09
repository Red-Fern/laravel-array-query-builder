<?php

namespace RedFern\ArrayQueryBuilder\Tests\Unit;

use RedFern\ArrayQueryBuilder\Conditions\WhereGroup;
use RedFern\ArrayQueryBuilder\Tests\TestCase;

class WhereGroupTest extends TestCase
{
    /** @test */
    public function it_creates_matching_query_based_on_conditions()
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

        $builder = (new WhereGroup($this->getEloquentBuilder(), $rules))->apply();

        $this->assertQueryEquals($expected, $builder);
    }

    /** @test */
    public function it_creates_a_matching_query_from_nested_condition()
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
                    'condition' => 'and',
                    'rules'     => [
                        [
                            'field'    => 'age',
                            'operator' => '>',
                            'value'    => 32,
                        ],
                        [
                            'field'    => 'age',
                            'operator' => '<',
                            'value'    => 54,
                        ],
                    ],
                ],
            ],
        ];

        $expected = $this->getEloquentBuilder()
            ->where('name', 'like', '%adam%')
            ->orWhere(function ($q) {
                $q->where('age', '>', 32)
                    ->where('age', '<', 54);
            });

        $builder = (new WhereGroup($this->getEloquentBuilder(), $rules))->apply();

        $this->assertQueryEquals($expected, $builder);
    }

    /** @test */
    public function it_creates_a_matching_and_query_with_relation()
    {
        $rules = [
            'condition' => 'and',
            'rules'     => [
                [
                    'field'    => 'name',
                    'operator' => 'like',
                    'value'    => '%adam%',
                ],
                [
                    'field'    => 'orders.order_date',
                    'operator' => '>',
                    'value'    => '2019-01-01 00:00:00',
                ],
            ],
        ];

        $expected = $this->getEloquentBuilder()
            ->where('name', 'like', '%adam%')
            ->whereHas('orders', function ($q) {
                $q->where('order_date', '>', '2019-01-01 00:00:00');
            });

        $builder = (new WhereGroup($this->getEloquentBuilder(), $rules))->apply();

        $this->assertQueryEquals($expected, $builder);
    }

    /** @test */
    public function it_creates_query_including_a_relation_group_with_single_criteria()
    {
        $rules = [
            'condition' => 'and',
            'rules'     => [
                [
                    'field'    => 'age',
                    'operator' => '>',
                    'value'    => 25,
                ],
                [
                    'field'    => 'age',
                    'operator' => '<',
                    'value'    => 50,
                ],
                [
                    'field'    => 'orders.order_date',
                    'operator' => '>',
                    'value'    => '2010-01-01 00:00:00',
                ],
            ],
        ];

        $expected = $this->getEloquentBuilder()
            ->where('age', '>', 25)
            ->where('age', '<', 50)
            ->whereHas('orders', function ($q) {
                $q->where('order_date', '>', '2010-01-01 00:00:00');
            });

        $actual = (new WhereGroup($this->getEloquentBuilder(), $rules))->apply();

        $this->assertQueryEquals($expected, $actual);
    }

    /** @test */
    public function it_creates_nested_query_along_with_relation_query()
    {
        $rules = [
            'condition' => 'and',
            'rules'     => [
                [
                    'condition' => 'or',
                    'rules'     => [
                        [
                            'field'    => 'name',
                            'operator' => '=',
                            'value'    => 'adam',
                        ],
                        [
                            'field'    => 'name',
                            'operator' => '=',
                            'value'    => 'liam',
                        ],
                    ],
                ],
                [
                    'field'    => 'orders.order_date',
                    'operator' => '>',
                    'value'    => '2010-01-01 00:00:00',
                ],
            ],
        ];

        $expected = $this->getEloquentBuilder()
            ->where(function ($q) {
                $q->where('name', '=', 'adam')
                    ->orWhere('name', '=', 'liam');
            })
            ->whereHas('orders', function ($q) {
                $q->where('order_date', '>', '2010-01-01 00:00:00');
            });

        $actual = (new WhereGroup($this->getEloquentBuilder(), $rules))->apply();

        $this->assertQueryEquals($expected, $actual);
    }

    /** @test */
    public function it_creates_or_query_with_nested_relation()
    {
        $rules = [
            'condition' => 'or',
            'rules'     => [
                [
                    'field'    => 'age',
                    'operator' => 'in',
                    'value'    => [20, 30, 40, 50],
                ],
                [
                    'condition' => 'and',
                    'rules'     => [
                        [
                            'field'    => 'orders.order_date',
                            'operator' => '>',
                            'value'    => '2010-01-01 00:00:00',
                        ],
                        [
                            'field'    => 'orders.order_date',
                            'operator' => '<',
                            'value'    => '2015-01-01 00:00:00',
                        ],
                        [
                            'field'    => 'name',
                            'operator' => '=',
                            'value'    => 'adam',
                        ],
                    ],
                ],

            ],
        ];

        $expected = $this->getEloquentBuilder()
            ->whereIn('age', [20, 30, 40, 50])
            ->orWhere(function ($q) {
                $q->whereHas('orders', function ($q2) {
                    $q2->where('order_date', '>', '2010-01-01 00:00:00')
                        ->where('order_date', '<', '2015-01-01 00:00:00');
                })
                ->where('name', '=', 'adam');
            });

//        $expected = $this->getEloquentBuilder()
//            ->whereIn('age', [20, 30, 40, 50])
//            ->orWhereHas('orders', function($q) {
//                $q->where('order_date', '>', '2010-01-01 00:00:00')
//                    ->where('order_date', '<', '2015-01-01 00:00:00');
//            });

        $actual = (new WhereGroup($this->getEloquentBuilder(), $rules))->apply();

        $this->assertQueryEquals($expected, $actual);
    }

    /** @test */
    public function it_combines_multiple_query_types()
    {
        $rules = [
            'condition' => 'or',
            'rules'     => [
                [
                    'field'    => 'age',
                    'operator' => 'in',
                    'value'    => [20, 30, 40, 50],
                ],
                [
                    'field'    => 'name',
                    'operator' => 'not null',
                ],
                [
                    'condition' => 'or',
                    'rules'     => [
                        [
                            'field'    => 'email',
                            'operator' => 'contains',
                            'value'    => 'red-fern.co.uk',
                        ],
                        [
                            'field'    => 'email',
                            'operator' => 'contains',
                            'value'    => 'hotmail.co.uk',
                        ],
                    ],
                ],
                [
                    'condition' => 'and',
                    'rules'     => [
                        [
                            'field'    => 'orders.order_date',
                            'operator' => '>',
                            'value'    => '2010-01-01 00:00:00',
                        ],
                        [
                            'field'    => 'orders.order_date',
                            'operator' => '<',
                            'value'    => '2015-01-01 00:00:00',
                        ],
                    ],
                ],

            ],
        ];

        $expected = $this->getEloquentBuilder()
            ->whereIn('age', [20, 30, 40, 50])
            ->orWhereNotNull('name')
            ->orWhere(function ($q) {
                $q->where('email', 'like', '%red-fern.co.uk%')
                    ->orWhere('email', 'like', '%hotmail.co.uk%');
            })
            ->orWhereHas('orders', function ($q) {
                $q->where('order_date', '>', '2010-01-01 00:00:00')
                    ->where('order_date', '<', '2015-01-01 00:00:00');
            });

        $actual = (new WhereGroup($this->getEloquentBuilder(), $rules))->apply();

        $this->assertQueryEquals($expected, $actual);
    }

    /** @test **/
    public function it_matches_a_query_from_multiple_nested_conditions()
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
                    'condition' => 'and',
                    'rules'     => [
                        [
                            'field'    => 'age',
                            'operator' => '>',
                            'value'    => 32,
                        ],
                        [
                            'field'    => 'age',
                            'operator' => '<',
                            'value'    => 54,
                        ],
                        [
                            'condition' => 'or',
                            'rules'     => [
                                [
                                    'field'    => 'email',
                                    'operator' => 'like',
                                    'value'    => '%red-fern.co.uk',
                                ],
                                [
                                    'field'    => 'email',
                                    'operator' => 'like',
                                    'value'    => '%hotmail.co.uk',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $expected = $this->getEloquentBuilder()
            ->where('name', 'like', '%adam%')
            ->orWhere(function ($q) {
                $q->where('age', '>', 32)
                    ->where('age', '<', 54)
                    ->where(function ($q2) {
                        $q2->where('email', 'like', '%red-fern.co.uk')
                            ->orWhere('email', 'like', '%hotmail.co.uk');
                    });
            });

        $builder = (new WhereGroup($this->getEloquentBuilder(), $rules))->apply();

        $this->assertQueryEquals($expected, $builder);
    }

    /** @test */
    public function it_creates_query_with_between_and_contains_condition()
    {
        $rules = [
            'condition' => 'and',
            'rules'     => [
                [
                    'field'    => 'age',
                    'operator' => 'between',
                    'value'    => [30, 50],
                ],
                [
                    'field'    => 'email',
                    'operator' => 'contains',
                    'value'    => 'red-fern.co.uk',
                ],
            ],
        ];

        $expected = $this->getEloquentBuilder()
            ->whereBetween('age', [30, 50])
            ->where('email', 'like', '%red-fern.co.uk%');

        $builder = (new WhereGroup($this->getEloquentBuilder(), $rules))->apply();

        $this->assertQueryEquals($expected, $builder);
    }

    /** @test */
    public function it_creates_a_null_check_on_relation_condition()
    {
        $rules = [
            'condition' => 'AND',
            'rules'     => [
                [
                    'field'    => 'name',
                    'operator' => 'contains',
                    'value'    => 'adam',
                ],
                [
                    'field'    => 'orders.order_date',
                    'operator' => 'null',
                ],
            ],
        ];

        $expected = $this->getEloquentBuilder()
            ->where('name', 'like', '%adam%')
            ->whereHas('orders', function ($q) {
                $q->whereNull('order_date');
            });

        $builder = (new WhereGroup($this->getEloquentBuilder(), $rules))->apply();

        $this->assertQueryEquals($expected, $builder);
    }

    /** @test */
    public function it_groups_query_relations()
    {
        $rules = [
            'condition' => 'and',
            'rules'     => [
                [
                    'field'    => 'age',
                    'operator' => '>',
                    'value'    => 29,
                ],
                [
                    'field'    => 'orders.order_date',
                    'operator' => '>',
                    'value'    => '2010-01-01 00:00:00',
                ],
                [
                    'field'    => 'orders.order_date',
                    'operator' => '<',
                    'value'    => '2010-12-31 23:59:59',
                ],
            ],
        ];

        $expected = $this->getEloquentBuilder()
            ->where('age', '>', '29')
            ->whereHas('orders', function ($q) {
                $q->where('order_date', '>', '2010-01-01 00:00:00')
                    ->where('order_date', '<', '2010-12-31 23:59:59');
            });

        $builder = (new WhereGroup($this->getEloquentBuilder(), $rules))->apply();

        $this->assertQueryEquals($expected, $builder);
    }

    /** @test */
    public function it_matches_nested_relation_query()
    {
        $rules = [
            'condition' => 'and',
            'rules'     => [
                [
                    'field'    => 'name',
                    'operator' => 'contains',
                    'value'    => 'adam',
                ],
                [
                    'condition' => 'and',
                    'rules'     => [
                        [
                            'field'    => 'orders.order_date',
                            'operator' => '>',
                            'value'    => '2010-01-01 00:00:00',
                        ],
                        [
                            'field'    => 'orders.order_date',
                            'operator' => '<',
                            'value'    => '2010-12-31 23:59:59',
                        ],
                    ],
                ],
            ],
        ];

        $expected = $this->getEloquentBuilder()
            ->where('name', 'like', '%adam%')
            ->whereHas('orders', function ($q) {
                $q->where('order_date', '>', '2010-01-01 00:00:00')
                    ->where('order_date', '<', '2010-12-31 23:59:59');
            });

        $builder = (new WhereGroup($this->getEloquentBuilder(), $rules))->apply();

//        dd($builder->toSql(), $expected->toSql());

        $this->assertQueryEquals($expected, $builder);
    }
}
