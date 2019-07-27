<?php

namespace TinyPixel\WordPress\Stripe;

use \Roots\Acorn\Application;

/**
 * Utilities
 *
 * @author  Kelly Mears <kelly@tinypixel.dev>
 * @license MIT
 * @since   1.0.0
 *
 * @package    wordpress
 * @subpackage stripe
 */
class Utilities
{
    /**
     * Construct
     *
     * @param \Roots\Acorn\Application               $app
     * @return \TinyPixel\WordPress\Stripe\Utilities $this
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        return $this;
    }

    /**
     * Return dollar amount in pennies
     *
     * @param  int $usd
     * @return int
     */
    public static function inPennies($usd)
    {
        return $usd * 100;
    }
}