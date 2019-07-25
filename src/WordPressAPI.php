<?php

namespace TinyPixel\WordPress\Stripe;

// WordPress
use \WP_Rest_Response;
use function \add_action;
use function \register_rest_route;

// Illuminate framework
use \Illuminate\Support\Collection;

// Roots
use \Roots\Acorn\Application;

// Internal
use \TinyPixel\WordPress\Stripe\Handler;

/**
 * WordPress API
 *
 * @author  Kelly Mears <kelly@tinypixel.dev>
 * @license MIT
 * @since   1.0.0
 *
 * @package    wordpress
 * @subpackage acorn-stripe
 */
class WordPressAPI
{
    /**
     * Construct
     *
     * @param \Roots\Acorn\Application $app
     * @return obj $this
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Initializes actions
     *
     * @return void
     */
    public function init()
    {
        $this->namespace = 'tiny-pixel';
        $this->handler   = $this->app['stripe.handler'];
        $this->clientId  = $this->handler->getClientId();

        add_action('rest_api_init', [$this, 'routes']);
    }

    /**
     * Sets up WP-API Routes
     *
     * @return void
     */
    public function routes()
    {
        register_rest_route($this->namespace, 'stripe', [
            [
                'methods'  => 'GET',
                'callback' => [$this, 'clientGet'],
            ],
            [
                'methods'  => 'POST',
                'callback' => [$this, 'clientPost'],
            ],
        ]);
    }

    /**
     * Callback for acorn/stripe/client GET requests
     *
     * @return \WP_REST_Response
     */
    public function clientGet()
    {
        if (isset($this->clientId)) {
            return new WP_REST_Response([
                'clientId' => $this->clientId,
            ], 200);
        }

        return new WP_REST_Response(['err' => 'Endpoint reached but no client id can be found.'], 400);
    }

    /**
     * Callback for acorn/stripe/client POST requests
     *
     * @param  obj $request
     * @return \WP_REST_Response
     */
    public function clientPost($request)
    {
        $parameters = $request->get_params();

        if (isset($parameters)) {
            if (isset($parameters->connectToken)
            && is_string($parameters->connectToken)) {
                $this->handler->setToken($token);
            }

            if (isset($parameters->amount)) {
                $response = $this->handler->makeTransaction($parameters->amount);
            }

            if (isset($response)) {
                return WP_REST_Response($response, 200);
            }

            return new WP_REST_Response(['err' => 'Transaction amount not defined'], 400);
        }
    }
}