<?php

namespace RedFern\ArrayQueryBuilder;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;
use RedFern\ArrayQueryBuilder\Conditions\WhereGroup;

class LaravelArrayQueryBuilderServiceProvider extends ServiceProvider
{
    /**
     * Register any services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/laravelarrayquerybuilder.php' => config_path('laravelarrayquerybuilder.php'),
        ], 'config');

        // Add macros
        Builder::macro('arrayWheres', function ($rules) {
            return (new WhereGroup($this, $rules))->apply();
        });
    }

    /**
     * Bootstrap any services services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravelarrayquerybuilder.php',
            'laravel-array-query-builder'
        );
    }
}
