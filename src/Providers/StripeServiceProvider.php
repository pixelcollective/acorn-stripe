<?php

namespace TinyPixel\WordPress\Stripe\Providers;

// Illuminate framework
use \Illuminate\Support\Collection;

// Roots
use \Roots\Acorn\ServiceProvider;
use function \Roots\{
    base_path,
    config_path,
};

// Stripe
use \Stripe\{
    Charge,
    Error,
};

// Internal
use \TinyPixel\WordPress\Stripe\{
    Assets,
    Transaction,
    WordPressAPI,
};

/**
 * Stripe Service Provider
 *
 * @author  Kelly Mears <kelly@tinypixel.dev>
 * @license MIT
 * @since   1.0.0
 *
 * @package    wordpress
 * @subpackage acorn-stripe
 */
class StripeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        require_once(__DIR__ . '/../../vendor/autoload.php');

        $this->app->singleton('stripe.charge', function () {
            return Charge::class;
        });

        $this->app->singleton('stripe.error.card', function () {
            return Error\Card::class;
        });

        $this->app->singleton('stripe.wp.transaction', function () {
            return new Transaction($this->app);
        });

        $this->app->singleton('stripe.wp.api', function () {
            return new WordPressAPI($this->app);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->doPublishables();

        $this->app->make('stripe.wp.transaction')->config(
            Collection::make($this->app['config']['services']['stripe'])
        )->init();

        $this->app->make('stripe.wp.api')->init();

        (new Assets())();
    }

    /**
     * Handles all publishables
     *
     * @return void
     */
    public function doPublishables()
    {
        $this->publishable = Collection::make();

        $this->publishableConfig();
        $this->publishableResources();
        $this->publishableComposers();

        $this->publishes($this->publishable->toArray());
    }

    /**
     * Publishable config
     *
     * @return void
     */
    public function publishableConfig()
    {
        $servicesConfig    = config_path('services.php');
        $publishableConfig = __DIR__ . '/../config/services.php';

        if (!file_exists($servicesConfig)) {
            $this->publishable->push([$publishableConfig => $servicesConfig]);
        } else {
            $this->mergeConfigFrom($publishableConfig, 'services');
        }
    }

    /**
     * Publishable assets
     *
     * @return void
     */
    public function publishableResources()
    {
        $appBase = base_path('resources');

        $this->publishes([
            __DIR__ . '/../resources/views'           => "{$appBase}/views/vendor/stripe",
            __DIR__ . '/../resources/assets/scripts/' => "{$appBase}/assets/scripts/vendor/stripe",
            __DIR__ . '/../resources/assets/styles/'  => "{$appBase}/assets/styles/vendor/stripe",
            __DIR__ . '/../resources/svg'             => "{$appBase}/svg/vendor/stripe",
            __DIR__ . '/../../dist/scripts.js'        => "{$appBase}/assets/scripts/vendor/stripe/bundle.js",
            __DIR__ . '/../../dist/stripe.css'        => "{$appBase}/assets/styles/vendor/stripe/bundle.css",
        ]);
    }

    /**
     * Publishable composers
     *
     * @return void
     */
    public function publishableComposers()
    {
        $appBase = base_path('app');

        $this->publishes([
            __DIR__ . '/../Composers/StripeForm.php' => "{$appBase}/Composers/StripeForm.php"
        ]);
    }
}
