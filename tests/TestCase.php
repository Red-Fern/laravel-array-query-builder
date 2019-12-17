<?php

namespace RedFern\ArrayQueryBuilder\Tests;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use RedFern\ArrayQueryBuilder\Tests\Models\TestModel;

class TestCase extends TestbenchTestCase
{
    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return ['RedFern\ArrayQueryBuilder\LaravelArrayQueryBuilderServiceProvider'];
    }

    /**
     * Mock a query builder instance.
     *
     * @return QueryBuilder
     */
    protected function getQueryBuilder()
    {
        $grammar = new Grammar();
        $processor = \Mockery::mock(Processor::class);

        $connection = \Mockery::mock(ConnectionInterface::class);

        return new QueryBuilder($connection, $grammar, $processor);
    }

    /**
     * Get an eloquent builder.
     *
     * @return EloquentBuilder
     */
    protected function getEloquentBuilder()
    {
        return (new EloquentBuilder($this->getQueryBuilder()))
            ->setModel(new TestModel());
    }

    /**
     * @param EloquentBuilder|QueryBuilder $query1
     * @param EloquentBuilder|QueryBuilder $query2
     */
    protected function assertQueryEquals($query1, $query2)
    {
        $queryComponents1 = $this->toQueryComponentsArray($query1);
        $queryComponents2 = $this->toQueryComponentsArray($query2);

        $this->assertEquals($queryComponents1, $queryComponents2);
    }

    /**
     * @param EloquentBuilder|QueryBuilder $query
     *
     * @return array
     */
    protected function toQueryComponentsArray($query)
    {
        if ($query instanceof EloquentBuilder) {
            $query = $query->getQuery();
        }

        $selectComponents = [
            'wheres',
        ];

        $rawQueryComponents = [];
        foreach ($selectComponents as $component) {
            if (! is_null($query->$component)) {
                $rawQueryComponents[$component] = $query->$component;
            }
        }

        return json_decode(json_encode($rawQueryComponents), true);
    }
}
