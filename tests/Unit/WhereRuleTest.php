<?php

namespace RedFern\ArrayQueryBuilder\Tests\Unit;

use RedFern\ArrayQueryBuilder\Tests\TestCase;
use RedFern\ArrayQueryBuilder\Conditions\WhereRule;

class WhereRuleTest extends TestCase
{
    /** @test */
    public function it_assigns_attributes_for_rule()
    {
        $builder = $this->getEloquentBuilder();

        $rule = new WhereRule($builder, [
            'field' => 'age',
            'operator' => '>',
            'value' => '28'
        ]);

        $this->assertEquals('age', $rule->field);
        $this->assertEquals('>', $rule->operator);
        $this->assertEquals('28', $rule->value);
    }

    /** @test */
    public function it_applies_rule_to_builder()
    {
        $builder = $this->getEloquentBuilder();

        $rule = new WhereRule($builder, [
            'field' => 'age',
            'operator' => '>',
            'value' => 30
        ]);

        $expected = $this->getEloquentBuilder()
            ->where('age', '>', 30);

        $this->assertQueryEquals($expected, $rule->apply());
    }

    /** @test */
    public function it_applies_a_null_check_to_builder()
    {
        $builder = $this->getEloquentBuilder();

        $rule = new WhereRule($builder, [
            'field' => 'age',
            'operator' => 'null'
        ]);

        $expected = $this->getEloquentBuilder()
            ->whereNull('age');

        $this->assertQueryEquals($expected, $rule->apply());
    }

    /** @test */
    public function it_applies_a_not_null_check_to_builder()
    {
        $builder = $this->getEloquentBuilder();

        $rule = new WhereRule($builder, [
            'field' => 'age',
            'operator' => 'not null'
        ]);

        $expected = $this->getEloquentBuilder()
            ->whereNotNull('age');

        $this->assertQueryEquals($expected, $rule->apply());
    }

    /** @test */
    public function it_applies_a_between_check_to_builder()
    {
        $builder = $this->getEloquentBuilder();

        $rule = new WhereRule($builder, [
            'field' => 'age',
            'operator' => 'between',
            'value' => [30, 50]
        ]);

        $expected = $this->getEloquentBuilder()
            ->whereBetween('age', [30, 50]);

        $this->assertQueryEquals($expected, $rule->apply());
    }

    /** @test */
    public function it_applies_an_in_check_to_builder()
    {
        $builder = $this->getEloquentBuilder();

        $rule = new WhereRule($builder, [
            'field' => 'age',
            'operator' => 'in',
            'value' => [30, 24]
        ]);

        $expected = $this->getEloquentBuilder()
            ->whereIn('age', [30, 24]);

        $this->assertQueryEquals($expected, $rule->apply());
    }

    /** @test */
    public function it_applies_a_not_in_check_to_builder()
    {
        $builder = $this->getEloquentBuilder();

        $rule = new WhereRule($builder, [
            'field' => 'age',
            'operator' => 'not in',
            'value' => [30, 48, 24]
        ]);

        $expected = $this->getEloquentBuilder()
            ->whereNotIn('age', [30, 48, 24]);

        $this->assertQueryEquals($expected, $rule->apply());
    }

    /** @test */
    public function it_applies_a_contains_check_to_builder()
    {
        $builder = $this->getEloquentBuilder();

        $rule = new WhereRule($builder, [
            'field' => 'name',
            'operator' => 'contains',
            'value' => 'Redfearn'
        ]);

        $expected = $this->getEloquentBuilder()
            ->where('name', 'like', '%Redfearn%');

        $this->assertQueryEquals($expected, $rule->apply());
    }

    /** @test */
    public function it_applies_a_has_relation_condition_check_to_builder()
    {
        $builder = $this->getEloquentBuilder();

        $rule = new WhereRule($builder, [
            'field' => 'orders',
            'operator' => 'has',
        ]);

        $expected = $this->getEloquentBuilder()
            ->whereHas('orders');

        $this->assertQueryEquals($expected, $rule->apply());
    }

    /** @test */
    public function it_applies_a_doesnt_have_relation_condition_check_to_builder()
    {
        $builder = $this->getEloquentBuilder();

        $rule = new WhereRule($builder, [
            'field' => 'orders',
            'operator' => 'doesnt have',
        ]);

        $expected = $this->getEloquentBuilder()
            ->whereDoesntHave('orders');

        $this->assertQueryEquals($expected, $rule->apply());
    }
}
