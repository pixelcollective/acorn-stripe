<?php

namespace TinyPixel\WordPress\Stripe\Providers;

// Roots
use \Roots\Acorn\ServiceProvider;
use function \Roots\config_path;

// Internal
use \TinyPixel\WordPress\Stripe\Stripe;

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
        $this->app->bind('')
        $this->app->singleton('omnipay', function () {
            return Stripe($this->app);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (!file_exists(config_path('services.php'))) {
            $this->publishes([
                __DIR__ . '/../config/services.php' => config_path('services.php'),
            ]);
        } else {
            $this->mergeConfigWith(__DIR__ . '/../config/services.php', 'services');
        }

        $this->app->make('Omnipay')::create('Stripe');
    }
}