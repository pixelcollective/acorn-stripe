<?php

namespace TinyPixel\WordPress\Stripe;

// WordPress
use function \add_action;
use function \wp_enqueue_script;

/**
 * Assets
 *
 * @author  Kelly Mears <kelly@tinypixel.dev>
 * @license MIT
 * @since   1.0.0
 *
 * @package    wordpress
 * @subpackage acorn-stripe
 */
class Assets
{
    /**
     * Invoke
     *
     * @return void
     */
    public function __invoke()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueueStripeLibrary']);
    }

    /**
     * Enqueues Stripe Library in Head
     *
     * @return void
     */
    public function enqueueStripeLibrary()
    {
        wp_enqueue_script('stripe/v3', 'https://js.stripe.com/v3/', false);
    }
}