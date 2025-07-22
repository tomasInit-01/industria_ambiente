<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use App\Observers\CotioInstanciaObserver;
use App\Models\CotioInstancia;  
use App\Models\CotioValorVariable;
use App\Observers\CotioValorVariableObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production') || str_contains(config('app.url'), 'ngrok-free.app')) {
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', true);
        }
    
        Paginator::useBootstrapFive();

        CotioInstancia::observe(CotioInstanciaObserver::class);
        CotioValorVariable::observe(CotioValorVariableObserver::class);
    }
}
