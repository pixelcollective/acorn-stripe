<?php

namespace TinyPixel\WordPress\Stripe;

// Illuminate Framework
use \Illuminate\Support\Collection;

// Stripe
use \Stripe\Stripe;
use \Stripe\Error;

// Roots
use \Roots\Acorn\Application;

// Internal
use Exception;

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
    /**
     * Classname
     */
    public static $classname = "\TinyPixel\WordPress\Stripe\Transaction";

    /**
     * Currency default
     *
     * @var string
     */
    public $defaultCurrency;

    /**
     * Optional parameters to be passed to Stripe
     *
     * @var  array
     * @link https://stripe.com/docs/api/charges/create
     */
    public $optionalParameters = [
        /**
         * A fee in cents that will be applied to the charge and transferred to the application owner’s Stripe account.
         * @link https://stripe.com/docs/connect/direct-charges#collecting-fees
         * @var integer 'application_fee_amount'
         */
        'application_fee_amount',

        /**
         * Whether to immediately capture the charge.
         * @var boolean 'capture'
         */
        'capture',

        /**
         * The ID of an existing customer that will be charged in this request.
         * @var string 'customer'
         */
        'customer',

        /**
         * An arbitrary string which you can attach to a Charge object. It is displayed when in the web interface alongside the charge.
         * @var string 'description'
         */
        'description',

        /**
         * Set of key-value pairs that you can attach to an object.
         * @var array 'metadata'
         */
        'metadata',

        /**
         * The Stripe account ID for which these funds are intended.
         * @var string 'on_behalf_of'
         */
        'on_behalf_of',

        /**
         * The email address to which the charge's receipt will be sent
         * @var string 'receipt_email'
         */
        'receipt_email',

        /**
         * Shipping information for the charge
         * @link https://stripe.com/docs/api/charges/create#create_charge-shipping
         * @var array 'shipping'
         */
        'shipping',

        /**
         * A payment source to be charged. This can be the ID of a card (i.e., credit or debit card), a bank account,
         * a source, a token, or a connected account.
         * @var string 'source'
         */
        'source',

        /**
         * An arbitrary string to be used as the dynamic portion of the full descriptor displayed on your customer’s credit card statement.
         * @var string 'statement_descriptor'
         */
        'statement_descriptor',

        /**
         * An optional dictionary including the account to automatically transfer to as part of a destination charge.
         * @link https://stripe.com/docs/api/charges/create#create_charge-transfer_data
         * @link https://stripe.com/docs/connect/destination-charges
         * @var array 'transfer_data'
         */
        'transfer_data',

        /**
         * A string that identifies this transaction as part of a group.
         * @link https://stripe.com/docs/api/charges/create#create_charge-transfer_group
         * @link https://stripe.com/docs/connect/charges-transfers#grouping-transactions
         * @var string 'transfer_group'
         */
        'transfer_group',
    ];

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

        try {
            $this->serverApiKey($this->settings->get('server_api_key'));
        } catch(Exception $err) {
            $this->handleError($err, "{$this::$classname}: A Stripe API key (secret) is required");
        }

        try {
            $this->clientApiKey($this->settings->get('client_api_key'));
        } catch (Exception $err) {
            $this->handleError($err, "{$this::$classname}: A Stripe API key (publishable) is required");
        }

        $defaultCurrency = $this->settings->get('default_currency');

        if(isset($defaultCurrency) && is_string($defaultCurrency)) {
            $this->defaultCurrency = $defaultCurrency;
        }

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
            $secretKey = $this->serverApiKey();
        } catch (Exception $err) {
            $this->handleError($err, "{$this::$classname}: Attempted to set an unsepcified key in init method");
        }

        if (isset($secretKey)) {
            Stripe::setApiKey($secretKey);
        }

        return $this;
    }

    /**
     * Handles Secret API key
     *
     * @param  string $serverApiKey
     * @return mixed{\TinyPixel\WordPress\Stripe\Transaction|string} {$this|$serverApiKey}
     */
    public function serverApiKey(string $serverApiKey = null)
    {
        if (isset($serverApiKey) && is_string($serverApiKey)) {
            $this->serverApiKey = $serverApiKey;

            return $this;
        }

        if (isset($this->serverApiKey)) {
            return $this->serverApiKey;
        } else {
            throw new Exception("{$this::$classname}: A Stripe API key (secret) is required");
        }
    }

    /**
     * Handles Publishable API Key
     *
     * @param  string $clientApiKey
     * @return mixed{int|object}
     */
    public function clientApiKey(string $clientApiKey = null)
    {
        if (isset($clientApiKey) && is_string($clientApiKey)) {
            $this->clientApiKey = $clientApiKey;

            return $this;
        }

        if (isset($this->clientApiKey)) {
            return $this->clientApiKey;
        } else {
            throw new Exception('A client API key is required to be set for Stripe transactions');
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
            throw new Exception('There is an issue generating the API token');
        }
    }

    /**
     * Handles transaction amount
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
            throw new Exception('There is no amount set for this transaction.');
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

        throw new Exception('Specify currency using the currency(string) method or in the configuration file');
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
            return $this->handleError($err, "{$this::$classname}: A token must be set");
        }

        // validate amount
        try {
            $amount = $this->amount();
        } catch (Exception $err) {
            return $this->handleError($err, "{$this::$classname}: An amount must be set.");
        }

        // validate currency
        try {
            $currency = $this->currency();
        } catch (Exception $err) {
            return $this->handleError($err, "{$this::$classname}: A currency must be specified.");
        }

        /**
         * Collects the minimum required parameters
         * for a Stripe\Charge request
         *
         * Also convert regular person dollars into
         * Stripe's weirdo penny piles before that gets bound up
         * in the collection.
         */
        $request = Collect([
            'source'   => $source,
            'amount'   => $this->inPennies($amount),
            'currency' => $currency,
        ]);

        /**
         * Attempts to append any additional parameters to the request
         */
        try {
            return $request = $this->optionalParameters($this->optionalParameters, $request);
        } catch(Exception $err) {
            return $this->handleError($err, "{$this::$classname}: addArguments method exception");
        }
    }

    /**
     * Adds optional parameters to request Collection
     *
     * @param  mixed{array|Illuminate\Support\Collection} $arguments
     * @param  \Illuminate\Support\Collection             $request
     * @return \Illuminate\Support\Collection             $request
     * @throws Exception
     */
    protected function optionalParameters($arguments, Collection $request)
    {
        // Cast arguments to collection if they aren't already
        if (is_array($arguments)) {
            $arguments = Collection::make($arguments);
        }

        /**
         * For each of the optional parameters check to see if a value has
         * been set. If something has been specified, call its handler and
         * and append the resultant value to the request.
         *
         * If the value is set but the method does not exist, throw an exception.
         */
        $arguments->each(function ($value, $argument) use ($request) {
            if (isset($value) && !is_null($value)) {
                if (is_callable([get_class($this), $value])) {
                    $val = $this->{$value}();
                }
            }

            if (isset($val) && $val) {
                $request->push([$argument => $val]);
            }
        });

        return $request;
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
            return $invalidTransaction = $this->handleError($err,
                "Error: chargeObject method"
            );
        }

        /**
         * Bails if charge is not of type \Illuminate\Support\Collection
         */
        if (!$charge instanceof \Illuminate\Support\Collection) {
            return $invalidTransaction = $this->handleError($err,
                "Error: chargeObject must return an Illuminate\Support\Collection instance"
            );
        }

        /**
         * Attempts to make the transaction and return the result
         */
        try {
        $transaction = $this->charge::create($charge->toArray());

        return $transaction;
        }
        /**
         * ...catches invalid card errors
         * @param \Stripe\Error\Card $err
         */
        catch (Error\Card $err) {
            return $invalidTransaction = $this->handleError($err,
                "Stripe error: Card error"
            );
        }
        /**
         * ...catches rate limit exceeded errors
         * @param \Stripe\Error\RateLimit $serverApiKey
         */
        catch (Error\RateLimit $err) {
            return $invalidTransaction = $this->handleError($err,
                "Stripe error: Rate limit exceeded"
            );
        }
        /**
         * ...catches invalid request errors
         * @param \Stripe\Error\InvalidRequest $err
         */
        catch (Error\InvalidRequest $err) {
            return $invalidTransaction = $this->handleError($err,
                "Stripe error: Invalid request"
            );
        }

        /**
         * ...catches authentication errors
         * @param \Stripe\Error\Authentication $err
         */
        catch (Error\Authentication $err) {
            return $invalidTransaction = $this->handleError($err,
                "Stripe error: Authentication"
            );
        }

        /**
         * ...catches API connection errors
         * @param \Stripe\Error\ApiConnection $err
         */
        catch (Error\ApiConnection $err) {
            return $invalidTransaction = $this->handleError($err,
                "Stripe error: API Connection"
            );
        }

        /**
         * ...catches generic Stripe errors
         * @param \Stripe\Error\Base $err
         */
        catch (Error\Base $err) {
            return $invalidTransaction = $this->handleError($err,
                "Stripe error: Base error encountered
            ");
        }

        /**
         * ...catches errors thrown by Stripe but caused by this implementation
         * @param Exception $err
         */
        catch (Exception $err) {
            return $invalidTransaction = $this->handleError($err,
                "\TinyPixel\WordPress\Stripe\Transaction error: thrown from Stripe\Charge"
            );
        }
    }

    /**
     * Returns information about error
     *
     * @param  \Stripe\Error                  $err
     * @param  string                         $label
     * @return \Illuminate\Support\Collection $error
     */
    public function handleError(mixed $err, string $label) {
        $errorBody = $err->getJsonBody();
        $errorStatus = $err->getHttpStatus();

        $error = Collection::make([
            'label'   => $label,
            'status'  => $errorStatus,
            'type'    => $errorBody['status'],
            'code'    => $errorBody['code'],
            'param'   => $errorBody['param'],
            'message' => $errorBody['message'],
        ]);

        return $error;
    }

    /**
     * Multiplies dollar amount by 100
     *
     * @param  int   $cents
     * @return float $dollar
     */
    protected function inPennies(int $dollars)
    {
        return $dollars * 100;
    }
}
