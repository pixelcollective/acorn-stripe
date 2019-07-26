<?php

namespace TinyPixel\WordPress\Stripe;

class Assets
{
    public function __invoke()
    {
        add_action('wp_enqueue_scripts', [
            $this, 'enqueueStripeLibrary',
        ]);
    }

    public function enqueueStripeLibrary()
    {
        wp_enqueue_script(
            'stripe/v3',
            'https://js.stripe.com/v3/',
            false
        );
    }
}