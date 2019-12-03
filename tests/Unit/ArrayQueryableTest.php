<?php

namespace RedFern\ArrayQueryBuilder\Tests\Unit;

use RedFern\ArrayQueryBuilder\Tests\Models\TestModel;
use RedFern\ArrayQueryBuilder\Tests\TestCase;

class ArrayQueryableTest extends TestCase
{
    /** @test **/
    public function it_applies_where_builder_through_static_model_method()
    {
        $query = TestModel::arrayWheres([
            'condition' => 'or',
            'rules' => [
                [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => 'adam'
                ],
                [
                    'field' => 'age',
                    'operator' => '>',
                    'value' => 25
                ],
                [
                    'condition' => 'and',
                    'rules' => [
                        [
                            'field' => 'orders.order_date',
                            'operator' => '>',
                            'value' => '2019-01-01 00:00:00'
                        ],
                        [
                            'field' => 'orders.order_date',
                            'operator' => '<',
                            'value' => '2019-05-01 00:00:00'
                        ]
                    ]
                ]
            ]
        ]);

        $expected = $this->getEloquentBuilder()
                        ->where('name', 'adam')
                        ->orWhere('age', '>', 25)
                        ->orWhereHas('orders', function($q){
                            $q->where('order_date', '>', '2019-01-01 00:00:00')
                                ->where('order_date', '<', '2019-05-01 00:00:00');
                        });

        $this->assertQueryEquals($expected, $query);
    }

    /** @test **/
    public function it_applies_where_builder_to_existing_model()
    {
        $model = new TestModel;

        $query = $model->newArrayWheres([
            'condition' => 'or',
            'rules' => [
                [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => 'adam'
                ],
                [
                    'field' => 'age',
                    'operator' => '>',
                    'value' => 25
                ],
                [
                    'condition' => 'and',
                    'rules' => [
                        [
                            'field' => 'orders.order_date',
                            'operator' => '>',
                            'value' => '2019-01-01 00:00:00'
                        ],
                        [
                            'field' => 'orders.order_date',
                            'operator' => '<',
                            'value' => '2019-05-01 00:00:00'
                        ]
                    ]
                ]
            ]
        ]);

        $expected = $this->getEloquentBuilder()
            ->where('name', 'adam')
            ->orWhere('age', '>', 25)
            ->orWhereHas('orders', function($q){
                $q->where('order_date', '>', '2019-01-01 00:00:00')
                    ->where('order_date', '<', '2019-05-01 00:00:00');
            });

        $this->assertQueryEquals($expected, $query);
    }
}
