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

/**
 * WordPress API
 *
 * @author  Kelly Mears <kelly@tinypixel.dev>
 * @license MIT
 * @since   1.0.0
 * @uses    Transaction
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
        $this->namespace   = 'tiny-pixel';
        $this->transaction = $this->app['stripe.wp.transaction'];
        $this->clientId    = $this->transaction->publicKey();

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
                'clientId' => $this->clientId
            ], 200);
        }

        return new WP_REST_Response([
            'err' => 'Endpoint reached but no client id can be found.'
        ], 400);
    }

    /**
     * Callback for acorn/stripe/client POST requests
     *
     * @param  obj $request
     * @return \WP_REST_Response
     */
    public function clientPost($request)
    {
        $parameters = (object) $request->get_params();

        if (isset($parameters)) {
            $this->transaction->token($parameters->stripeToken)
                              ->amount($parameters->amount)
                              ->setOptional('description', 'test');

            $response = $this->transaction->transaction();

            if (isset($response)) {
                return new WP_REST_Response($response, 200);
            }

            return new WP_REST_Response([
                'err' => 'Problem with the transaction'
            ], 400);
        }
    }
}