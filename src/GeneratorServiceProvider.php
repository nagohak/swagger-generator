<?php

namespace DEVJS\SwaggerGenerator;

use Illuminate\Support\ServiceProvider;

class GeneratorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Generator::class, function ($app){
            return new Generator();
        });
    }

    public function boot(): void
    {

    }
}