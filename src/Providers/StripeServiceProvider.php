<?php

namespace TinyPixel\WordPress\Stripe\Providers;

// Illuminate framework
use \Illuminate\Support\Collection;

// Roots
use \Roots\Acorn\ServiceProvider;
use function \Roots\config_path;
use function \Roots\base_path;

// Internal
use \TinyPixel\WordPress\Stripe\Handler;
use \TinyPixel\WordPress\Stripe\WordPressAPI;

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
        $this->app->singleton('stripe.handler', function () {
            return new Handler($this->app);
        });

        $this->app->singleton('stripe.wpapi', function () {
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
        $this->publishable = Collection::make();

        $this->publishableConfig();
        $this->publishableResources();
        $this->publishes($this->publishable->toArray());

        $this->app->make('stripe.handler')->config(
            Collection::make($this->app['config']['services']['stripe'])
        );

        $this->app->make('stripe.wpapi')->init();
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
}
