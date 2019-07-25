<?php

namespace TinyPixel\WordPress\Stripe;

use \Roots\Acorn\Application;

/**
 * Stripe
 *
 * @author  Kelly Mears <kelly@tinypixel.dev>
 * @license MIT
 * @since   1.0.0
 *
 * @package    wordpress
 * @subpackage acorn-stripe
 */
class Stripe
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

    public function instantiate()
    {
        if ($apiKey = $this->settings->get('stripe.api_key')) {
            $this->gateway = Omnipay::create('Stripe');

            $this->gateway->setApiKey($this->settings->get('stripe.api_key'));
        } else {
            throw new Exception('An API is required to be set for Stripe transactions');
        }
    }

    public function setCard(array $card)
    {
        $this->card = [
            'number'      => $card['number'],
            'expiryMonth' => $card['expiryMonth'],
            'expiryYear'  => $card['expiryYear'],
            'cvv'         => $card['cvv'],
        ];
    }

    public function makeTransaction($amount, $currency = 'USD')
    {
        if (isset($this->card)) {
            return $this->processResponse($this->gateway->purchase([
                'amount'   => $amount,
                'currency' => $currency,
                'card'     => $this->card,
            ]));
        }
    }

    private function processResponse($response)
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