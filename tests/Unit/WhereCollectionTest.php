<?php

namespace RedFern\ArrayQueryBuilder\Tests\Unit;

use RedFern\ArrayQueryBuilder\Tests\TestCase;
use RedFern\ArrayQueryBuilder\Conditions\WhereCollection;

class WhereCollectionTest extends TestCase
{
    /** @test */
    public function it_returns_false_if_contents_are_not_relational()
    {
        $rules = [
            [
                "field" => "age",
                "operator" => ">",
                "value" => 25
            ]
        ];

        $collection = new WhereCollection($rules);

        $this->assertFalse($collection->isRelational());
    }

    /** @test */
    public function it_returns_true_if_contents_are_relational()
    {
        $rules = [
            [
                "field" => "orders.order_date",
                "operator" => ">",
                "value" => '2010-01-01'
            ]
        ];

        $collection = new WhereCollection($rules);

        $this->assertTrue($collection->isRelational());
    }

    /** @test */
    public function it_returns_true_if_contents_are_a_list_are_a_relational_group()
    {
        $rules = [
            [
                'condition' => 'and',
                'rules' => [
                    [
                        'field' => 'orders.start_date',
                        'operator' => '>',
                        'value' => '2010-01-01 00:00:00'
                    ],
                    [
                        'field' => 'orders.start_date',
                        'operator' => '<',
                        'value' => '2015-01-01 00:00:00'
                    ]
                ]
            ]
        ];

        $collection = new WhereCollection($rules);

        $this->assertTrue($collection->isRelationalGroup());
    }

    /** @test */
    public function it_returns_false_if_relational_group_contains_non_relational_field()
    {
        $rules = [
            [
                'condition' => 'and',
                'rules' => [
                    [
                        'field' => 'orders.start_date',
                        'operator' => '>',
                        'value' => '2010-01-01 00:00:00'
                    ],
                    [
                        'field' => 'orders.start_date',
                        'operator' => '<',
                        'value' => '2015-01-01 00:00:00'
                    ],
                    [
                        'field' => 'age',
                        'operator' => '>',
                        'value' => 25
                    ]
                ]
            ]
        ];

        $collection = new WhereCollection($rules);

        $this->assertFalse($collection->isRelationalGroup());
    }
}
