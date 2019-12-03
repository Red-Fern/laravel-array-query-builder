<?php

namespace RedFern\ArrayQueryBuilder\Tests\Unit;

use RedFern\ArrayQueryBuilder\Conditions\RelationGroup;
use RedFern\ArrayQueryBuilder\Tests\TestCase;

class RelationGroupTest extends TestCase
{
    /** @test */
    public function it_groups_relation_queries()
    {
        $rules = [
            [
                'field' => 'orders.order_date',
                'operator' => '>',
                'value' => '2010-01-01 00:00:00'
            ],
            [
                'field' => 'orders.order_date',
                'operator' => '<',
                'value' => '2010-12-31 23:59:59',
            ]
        ];

        $expected = $this->getEloquentBuilder()
            ->whereHas('orders', function($q){
                $q->where('order_date', '>', '2010-01-01 00:00:00')
                    ->where('order_date', '<', '2010-12-31 23:59:59');
            });

        $builder = (new RelationGroup($this->getEloquentBuilder(), $rules))->apply();

        $this->assertQueryEquals($expected, $builder);
    }
}
