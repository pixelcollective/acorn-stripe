<?php

namespace TinyPixel\WordPress\Stripe;

use \Exception;

// Illuminate Framework
use \Illuminate\Support\Collection;

// Stripe
use \Stripe\{
    Stripe,
    Error,
};

// Roots
use \Roots\Acorn\Application;

// Internal
use \TinyPixel\WordPress\Stripe\{
    Traits,
    Exceptions\StripeException,
};

/**
 * Implements Stripe transactions
 *
 * @author  Kelly Mears <kelly@tinypixel.dev>
 * @since   1.0.0
 * @license MIT
 * @see     \Stripe\Charge
 * @link    https://stripe.com/docs/api/charges/create
 *
 * @package    wordpress
 * @subpackage acorn-stripe
 */
class Transaction
{
    use Traits\OptionalParameters;

    /**
     * Classname
     *
     * @static string
     */
    public static $classname = "\TinyPixel\WordPress\Stripe\Transaction";

    /**
     * Currency default
     *
     * @var string
     */
    public $defaultCurrency;

    /**
     * Secret API key
     *
     * @var string
     */
    protected $secretKey;

    /**
     * Client API key
     *
     * @var string
     */
    protected $publicKey;

    /**
     * Construct
     *
     * @param  \Roots\Acorn\Application                $app
     * @return \TinyPixel\WordPress\Stripe\Transaction $this
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Configures class
     *
     * @return \TinyPixel\WordPress\Stripe\Transaction $this
     */
    public function config()
    {
        $this->settings = Collection::make(
            $this->app['config']['services']['stripe']
        );

        $this->util   = $this->app->make('stripe.wp.utilities');
        $this->charge = $this->app->make('stripe.charge');
    }


    /**
     * Initializes Stripe
     *
     * @return \TinyPixel\WordPress\Stripe\Transaction $this
     */
    public function init()
    {
        try {
            $this->secretKey($this->settings->get('api_secret'));
        } catch (Exception $err) {
            return new Exception("{$this::$classname}: A secret key is required");
        }

        try {
            $this->publicKey($this->settings->get('api_publishable'));
        } catch (Exception $err) {
            return new Exception("{$this::$classname}: A public key is required");
        }

        $defaultCurrency = $this->settings->get('default_currency');

        if (isset($defaultCurrency) && is_string($defaultCurrency)) {
            $this->defaultCurrency = $defaultCurrency;
        }

        if (isset($this->secretKey) && !is_null($this->secretKey)) {
            Stripe::setApiKey($this->secretKey);
        }
    }

    /**
     * Secret API key
     *
     * @param  string $secretKey
     * @return mixed{\TinyPixel\WordPress\Stripe\Transaction|string} {$this|$secretKey}
     */
    protected function secretKey(string $secretKey)
    {
        if (isset($secretKey) && is_string($secretKey)) {
            $this->secretKey = $secretKey;

            return $this;
        }

        if (isset($this->secretKey)) {
            return $this->secretKey;
        } else {
            throw new Exception("{$this::$classname}: A secret key is required");
        }
    }

    /**
     * Publishable API Key
     *
     * @param  string $publicKey
     * @return mixed{int|object}
     */
    public function publicKey(string $publicKey = null)
    {
        if (isset($publicKey) && is_string($publicKey)) {
            $this->publicKey = $publicKey;

            return $this;
        }

        if (isset($this->publicKey)) {
            return $this->publicKey;
        } else {
            throw new Exception("{$this::$classname} A client key is required");
        }
    }

    /**
     * Handles API Token
     *
     * @param  string $token
     * @return mixed{int|object}
     */
    public function token(string $token = null)
    {
        if (isset($token) && is_string($token)) {
            $this->token = $token;

            return $this;
        }

        if (isset($this->token)) {
            return $this->token;
        } else {
            throw new Exception("{$this::$classname} There is an issue generating the API token");
        }
    }

    /**
     * Sets transaction amount
     *
     * @param  int $amount
     * @return mixed{int|object}
     */
    public function amount(int $amount = null)
    {
        if (isset($amount) && is_int($amount)) {
            $this->amount = $amount;

            return $this;
        }

        if (isset($this->amount)) {
            return $this->amount;
        } else {
            throw new Exception("{$this::$classname} There is no amount set for this transaction.");
        }
    }

    /**
     * Handles required transaction parameter: currency
     *
     * @param  int    $amount
     * @return string $currency
     */
    public function currency(int $currency = null)
    {
        if (isset($currency) && is_string($currency)) {
            $this->currency = $currency;
        } elseif (isset($this->defaultCurrency)) {
            $this->currency = $this->defaultCurrency;
        }

        if (isset($this->currency)) {
            return $this->currency;
        }

        throw new Exception("{$this::$classname}: Specify currency (USD, GB, etc.)");
    }

    /**
     * Prepares parameters for transaction method
     *
     * @return \Illuminate\Support\Collection $request
     */
    public function chargeObject()
    {
        // convert dollar amount to pennies for Stripe API
        $this->amount = $this->util::inPennies($this->amount);

        try {
           $required = $this->validateRequisiteParams();
        } catch (Exception $err) {
            throw new Exception ($err);
        }

        $request = Collect($required);

        /**
         * Attempts to append any additional parameters to the request
         */
        try {
            return $request = $this->optionalParameters($this->optionalParameters, $request);
        } catch(Exception $err) {
            return new Exception("{$this::$classname}: addArguments method exception");
        }
    }

    /**
     * Validate requisite params
     *
     * @return array
     */
    public function validateRequisiteParams()
    {
        // validate token
        try {
            $source = $this->token();
        } catch (Exception $err) {
            throw new Exception("{$this::$classname}: A token must be set");
        }

        // validate amount
        try {
            $amount = $this->amount();
        } catch (Exception $err) {
            throw new Exception("{$this::$classname}: An amount must be set.");
        }

        // validate currency
        try {
            $currency = $this->currency();
        } catch (Exception $err) {
            throw new Exception("{$this::$classname}: A currency must be specified.");
        }

        return [
            'source'   => $source,
            'amount'   => $amount,
            'currency' => $currency,
        ];
    }

    /**
     * Makes Stripe transaction
     *
     * @return \Illuminate\Support\Collection $transaction
     * @return \Illuminate\Support\Collection $invalidTransaction
     */
    public function transaction()
    {
        /**
         * Attempts to gather details of the current transaction
         */
        try {
            $charge = $this->chargeObject();
        } catch(Exception $err) {
            return new Exception("Error: chargeObject method");
        }

        /**
         * Bails if charge is not of type \Illuminate\Support\Collection
         */
        if (!$charge instanceof \Illuminate\Support\Collection) {
            return new Exception("Error: chargeObject must return a Collection");
        }

        /**
         * Attempts to make the transaction and return the result
         */
        try {
            return $this->charge::create($charge->toArray());
        }

        /**
         * ...catches invalid card errors
         */
        catch (Error\Card $err) {
            throw new StripeException($err, "Stripe error: Card error");
        }

        /**
         * ...catches rate limit exceeded errors
         */
        catch (Error\RateLimit $err) {
            throw new StripeException($err, "Stripe error: Rate limit exceeded");
        }

        /**
         * ...catches invalid request errors
         */
        catch (Error\InvalidRequest $err) {
            throw new StripeException($err, "Stripe error: Invalid request");
        }

        /**
         * ...catches authentication errors
         */
        catch (Error\Authentication $err) {
            throw new StripeException($err, "Stripe error: Authentication");
        }

        /**
         * ...catches API connection errors
         */
        catch (Error\ApiConnection $err) {
            throw new StripeException($err, "Stripe error: API Connection");
        }

        /**
         * ...catches generic Stripe errors
         */
        catch (Error\Base $err) {
            throw new StripeException($err, "Stripe error: Base error encountered");
        }

        /**
         * ...catches errors thrown by Stripe but caused by this implementation
         */
        catch (Exception $err) {
            throw new Exception($err, "Transaction error: thrown from Stripe\Charge");
        }
    }
}
