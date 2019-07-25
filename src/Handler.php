<?php

namespace TinyPixel\WordPress\Stripe;

use \Omnipay\Omnipay;
use \OmniPay\Stripe;

use \Roots\Acorn\Application;

/**
 * Handler
 *
 * @author  Kelly Mears <kelly@tinypixel.dev>
 * @license MIT
 * @since   1.0.0
 *
 * @package    wordpress
 * @subpackage acorn-stripe
 */
class Handler
{
    /**
     * Construct
     *
     * @param  \Roots\Acorn\Application $app
     * @return obj $this
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        return $this;
    }

    public function config(\Illuminate\Support\Collection $config)
    {
        $this->settings = $config;
    }

    /**
     * Initializes class
     *
     * @return void
     */
    public function init()
    {
        if ($apiKey = $this->settings->get('server_api_key')) {
            $this->gateway = Omnipay::create('Stripe');

            $this->gateway->setApiKey($apiKey);
        } else {
            throw new Exception('An API is required to be set for Stripe transactions');
        }
    }

    /**
     * Get Client Id
     *
     * @return void
     */
    public function getClientId()
    {
        return $this->settings->get('client_api_key');
    }

    /**
     * Set API Token
     *
     * @param  string $token
     * @return void
     */
    public function setToken(string $token)
    {
        if (isset($token)) {
            $this->token = $token;
        }
    }

    /**
     * Sets currency type
     *
     * @param  string $currency
     * @return void
     */
    public function setCurrency($currency)
    {
        if (isset($currency)) {
            $this->currency = $currency;
        }
    }

    /**
     * Makes transaction with Stripe service
     *
     * @param  int $amount
     * @return void
     */
    public function makeTransaction(int $amount)
    {
        if (isset($this->token)) {
            return $this->processStripeResponse(
                $this->gateway->purchase([
                    'amount'   => $amount,
                    'currency' => $currency ? $currency : 'USD',
                    'token'    => $token,
                ])->send()
            );
        }
    }

    /**
     * Processes response from Stripe service
     *
     * @param  obj  $response
     * @return mixed
     */
    private function processStripeResponse($response)
    {
        if ($response->isSuccessful()) {
            return $response;
        }

        if ($response->isRedirect()) {
            $response->redirect();
        }

        return $response->getMessage();
    }
}
