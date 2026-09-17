<?php

namespace Yugo\Quran;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

final class QuranServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/quran.php', 'quran');

        $this->app->singleton('quran', fn (Container $app): QuranManager => new QuranManager($app));
        $this->app->alias('quran', QuranManager::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/quran.php' => config_path('quran.php'),
        ], 'quran-config');
    }
}
