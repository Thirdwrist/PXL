<?php

namespace App\Providers;

use App\Repositories\CreditCardRepository;
use App\Repositories\ImportRepository;
use App\Repositories\UserRepository;
use App\Services\Import\ImportManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ImportManager::class, function ($app){
           return new ImportManager($app->make(ImportRepository::class), $app->make(UserRepository::class), $app->make(CreditCardRepository::class));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
