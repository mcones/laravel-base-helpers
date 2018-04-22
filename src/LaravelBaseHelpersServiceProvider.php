<?php

namespace mcones\LaravelBaseHelpers;


use Illuminate\Support\ServiceProvider;
use Mcones\LaravelBaseHelpers\Commands\GenerateServiceCommand;

class LaravelBaseHelpersServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateServiceCommand::class,
            ]);
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}