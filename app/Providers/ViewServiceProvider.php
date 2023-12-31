<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();

        Blade::if('route', function ($routeName, $routeParams = null) {
            if($routeParams) {
                return Route::is($routeName) && Route::current()->parameters() == $routeParams;
            }
            return Route::is($routeName);
        });

        Blade::directive('spaceless', function () {
            return '<?php ob_start(); ?>';
        });

        Blade::directive('endspaceless', function () {
            return '<?php echo trim(preg_replace(\'/>\s+</\', \'><\', ob_get_clean())); ?>';
        });

        Blade::include('includes.flash-messages', 'flash');
    }
}
