<?php

namespace TinyPixel\WordPress\Stripe;

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
    Exceptions\Exception,
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
    protected $secretApiKey;

    /**
     * Client API key
     *
     * @var string
     */
    protected $publicApiKey;

    /**
     * Construct
     *
     * @param  \Roots\Acorn\Application                $app
     * @return \TinyPixel\WordPress\Stripe\Transaction $this
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        return $this;
    }

    /**
     * Configures class
     *
     * @param  \Illuminate\Support\Collection          $config
     * @return \TinyPixel\WordPress\Stripe\Transaction $this
     */
    public function config(Collection $config)
    {
        $this->settings = $config;

        $this->setStripeKeys();
        $this->setDefaultCurrency();

        $this->util   = $this->app->make('stripe.wp.utilities');
        $this->charge = $this->app->make('stripe.charge');

        return $this;
    }

    /**
     * Initializes Stripe
     *
     * @return \TinyPixel\WordPress\Stripe\Transaction $this
     */
    public function init()
    {
        try {
            $this->secretKey = $this->secretApiKey();
        } catch (Exception $err) {
            return new Exception("{$this::$classname}: Attempted to set an unsepcified key in init method");
        }

        if (isset($this->secretKey) && !is_null($this->secretKey)) {
            Stripe::setApiKey($this->secretKey);
        }

        return $this;
    }

    /**
     * Set Stripe keys
     *
     * @return void
     */
    private function setStripeKeys()
    {
        try {
            $this->secretApiKey($this->settings->get('server_api_key'));
        } catch (Exception $err) {
            return new Exception("{$this::$classname}: A Stripe API key (secret) is required");
        }

        try {
            $this->publicApiKey($this->settings->get('client_api_key'));
        } catch (Exception $err) {
            return new Exception("{$this::$classname}: A Stripe API key (publishable) is required");
        }
    }

    /**
     * Secret API key
     *
     * @param  string $secretApiKey
     * @return mixed{\TinyPixel\WordPress\Stripe\Transaction|string} {$this|$secretApiKey}
     */
    public function secretApiKey(string $secretApiKey = null)
    {
        if (isset($secretApiKey) && is_string($secretApiKey)) {
            $this->secretApiKey = $secretApiKey;

            return $this;
        }

        if (isset($this->secretApiKey)) {
            return $this->secretApiKey;
        } else {
            throw new Exception("{$this::$classname}: A Stripe API key (secret) is required");
        }
    }

    /**
     * Publishable API Key
     *
     * @param  string $publicApiKey
     * @return mixed{int|object}
     */
    public function publicApiKey(string $publicApiKey = null)
    {
        if (isset($publicApiKey) && is_string($publicApiKey)) {
            $this->publicApiKey = $publicApiKey;

            return $this;
        }

        if (isset($this->publicApiKey)) {
            return $this->publicApiKey;
        } else {
            throw new Exception("{$this::$classname} A client API key is required to be set for Stripe transactions");
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
     * Sets default currency
     *
     * @return void
     */
    public function setDefaultCurrency()
    {
        $defaultCurrency = $this->settings->get('default_currency');

        if (isset($defaultCurrency) && is_string($defaultCurrency)) {
            $this->defaultCurrency = $defaultCurrency;
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

        throw new Exception("{$this::$classname}: Specify currency using the currency(string) method or in the configuration file");
    }

    /**
     * Prepares parameters for transaction method
     *
     * @return \Illuminate\Support\Collection $request
     */
    public function chargeObject()
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

        // convert dollar amount to pennies for Stripe API
        $amount = $this->util::inPennies($amount);

        /**
         * Collects the minimum required parameters
         * for a Stripe\Charge request
         */
        $request = Collect([
            'source'   => $source,
            'amount'   => $amount,
            'currency' => $currency,
        ]);

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
            return new Exception("Error: chargeObject must return an Illuminate\Support\Collection instance");
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
            throw new Exception($err, "\TinyPixel\WordPress\Stripe\Transaction error: thrown from Stripe\Charge");
        }
    }
}
