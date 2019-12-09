<?php

namespace RedFern\ArrayQueryBuilder;

use Illuminate\Support\ServiceProvider;

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
