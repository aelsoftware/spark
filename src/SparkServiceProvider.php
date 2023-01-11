<?php

namespace Spark;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Paddle\Cashier;
use Laravel\Paddle\Events\SubscriptionCancelled as CashierSubscriptionCancelled;
use Laravel\Paddle\Events\SubscriptionCreated as CashierSubscriptionCreated;
use Laravel\Paddle\Events\SubscriptionUpdated as CashierSubscriptionUpdated;
use Spark\Contracts\Actions\GeneratesSubscriptionPayLinks;
use Spark\Events\SubscriptionCancelled;
use Spark\Events\SubscriptionCreated;
use Spark\Events\SubscriptionUpdated;

class SparkServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Cashier::ignoreMigrations();

        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__.'/../config/spark.php', 'spark');
        }

        $this->app->singleton('spark.manager', SparkManager::class);
        $this->app->singleton(FrontendState::class);

        app()->singleton(
            GeneratesSubscriptionPayLinks::class,
            Actions\GenerateSubscriptionPayLink::class
        );

        $this->registerCommands();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'spark');

        $this->configureRoutes();
        $this->configureListeners();
        $this->configureMigrations();
        $this->configureTranslations();
        $this->configurePublishing();
    }

    /**
     * Register the routes offered by the application.
     *
     * @return void
     */
    protected function configureRoutes()
    {
        Route::group([], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/routes.php');
        });
    }

    /**
     * Register the Spark events.
     *
     * @return void
     */
    protected function configureListeners()
    {
        $events = $this->app->make(Dispatcher::class);

        $events->listen(CashierSubscriptionCreated::class, function ($event) use ($events) {
            $events->dispatch(new SubscriptionCreated($event->billable));
        });

        $events->listen(CashierSubscriptionUpdated::class, function ($event) use ($events) {
            $events->dispatch(new SubscriptionUpdated($event->subscription->billable));
        });

        $events->listen(CashierSubscriptionCancelled::class, function ($event) use ($events) {
            $events->dispatch(new SubscriptionCancelled($event->subscription->billable));
        });
    }

    /**
     * Configure Spark migrations.
     *
     * @return void
     */
    protected function configureMigrations()
    {
        if (Spark::runsMigrations() && $this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    /**
     * Configure Spark translations.
     *
     * @return void
     */
    protected function configureTranslations()
    {
        $this->loadJsonTranslationsFrom(lang_path('spark'));
    }

    /**
     * Configure publishing for the package.
     *
     * @return void
     */
    protected function configurePublishing()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/spark.php' => config_path('spark.php'),
        ], 'spark-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/spark'),
        ], 'spark-views');

        $this->publishes([
            __DIR__.'/../stubs/en.json' => lang_path('spark/en.json'),
        ], 'spark-lang');

        $this->publishes([
            __DIR__.'/../stubs/SparkServiceProvider.php' => app_path('Providers/SparkServiceProvider.php'),
        ], 'spark-provider');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'spark-migrations');
    }

    /**
     * Register the Spark Artisan commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\InstallCommand::class,
            ]);
        }
    }
}
