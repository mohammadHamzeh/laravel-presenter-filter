<?php
namespace mhamzeh\packageFp;
use Illuminate\Support\ServiceProvider;
use mhamzeh\packageFp\Commands\FilterMake;
use mhamzeh\packageFp\Commands\PresenterMake;


class PresenterFilterServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->commands([
            FilterMake::class,
            PresenterMake::class
        ]);
        $this->publishes([
            __DIR__.'/../config' => config_path('/'),
        ]);
    }

    public function register()
    {
        //
    }


}