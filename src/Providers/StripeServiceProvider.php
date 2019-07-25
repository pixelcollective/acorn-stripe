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
        $this->publishableConfig();
        $this->publishableAssets();

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
            $this->publishes([$publishableConfig => $servicesConfig]);
        } else {
            $this->mergeConfigFrom($publishableConfig, 'services');
        }
    }

    /**
     * Publishable assets
     *
     * @return void
     */
    public function publishableAssets()
    {
        $appResources         = base_path('resources/vendor/stripe');
        $publishableResources = __DIR__ . '/../resources/vendor/stripe';

        $this->publishes([$publishableResources => $appResources]);
    }
}
