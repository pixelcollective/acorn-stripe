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

    /**
     * unCamelCaser
     *
     * @return string
     */
    public static function snakeToCamel(string $snake)
    {
        return str_replace('_', '', ucwords($key, '_'));
    }

    /**
     * from Camel to Snake
     */
    public static function camelToSnake(string $camel)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $camel));
    }
}