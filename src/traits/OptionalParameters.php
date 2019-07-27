<?php

namespace TinyPixel\WordPress\Stripe\Traits;

// Illuminate framework
use \Illuminate\Support\Collection;

// Internal
use \TinyPixel\WordPress\Stripe\Utilities;

/**
 * Trait: optional stripe api req parameters
 *
 */
trait OptionalParameters
{
    /**
     * Optional parameters to be passed to Stripe
     *
     * @var  array
     * @link https://stripe.com/docs/api/charges/create
     */
    public $optionalParameters = [
        /**
         * A fee in cents that will be applied to the charge and transferred
         * to the application owner’s Stripe account.
         *
         * @link https://stripe.com/docs/connect/direct-charges#collecting-fees
         * @var integer 'application_fee_amount'
         */
        'application_fee_amount' => null,

        /**
         * Whether to immediately capture the charge.
         *
         * @var boolean 'capture'
         */
        'capture' => null,

        /**
         * The ID of an existing customer that will be charged in this request.
         *
         * @var string 'customer'
         */
        'customer' => null,

        /**
         * An arbitrary string which you can attach to a Charge object. It is
         * displayed when in the web interface alongside the charge.
         *
         * @var string 'description'
         */
        'description' => null,

        /**
         * Set of key-value pairs that you can attach to an object.
         *
         * @var array 'metadata'
         */
        'metadata' => null,

        /**
         * The Stripe account ID for which these funds are intended.
         *
         * @var string 'on_behalf_of'
         */
        'on_behalf_of' => null,

        /**
         * The email address to which the charge's receipt will be sent
         *
         * @var string 'receipt_email'
         */
        'receipt_email' => null,

        /**
         * Shipping information for the charge
         * @link https://stripe.com/docs/api/charges/create#create_charge-shipping
         *
         * @var array 'shipping'
         */
        'shipping' => null,

        /**
         * A payment source to be charged. This can be the ID of a card
         * (i.e., credit or debit card), a bank account,
         * a source, a token, or a connected account.
         *
         * @var string 'source'
         */
        'source' => null,

        /**
         * An arbitrary string to be used as the dynamic portion of the full descriptor
         * displayed on your customer’s credit card statement.
         *
         * @var string 'statement_descriptor'
         */
        'statement_descriptor' => null,

        /**
         * An optional dictionary including the account to automatically transfer to as part of a destination charge.
         * @link https://stripe.com/docs/api/charges/create#create_charge-transfer_data
         * @link https://stripe.com/docs/connect/destination-charges
         *
         * @var array 'transfer_data'
         */
        'transfer_data' => null,

        /**
         * A string that identifies this transaction as part of a group.
         * @link https://stripe.com/docs/api/charges/create#create_charge-transfer_group
         * @link https://stripe.com/docs/connect/charges-transfers#grouping-transactions
         *
         * @var string 'transfer_group'
         */
        'transfer_group' => null,
    ];

    /**
     * Setter intended for use with optional parameters
     *
     * @param  string $parameter
     * @param  mixed  $value
     * @return object $this
     */
    public function setOptional($parameter, $value)
    {
        if (isset($parameter, $value)) {
            $this->optionalParameters[$parameter] = $value;
        } else {
            throw new Exception('Problem setting parameter');
        }

        return $this;
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
        /**
         * For each of the optional parameters check to see if a value has
         * been set. If something has been specified, call its handler and
         * and append the resultant value to the request.
         *
         * If the value is set but the method does not exist, throw an exception.
         */
        Collection::make($arguments)->each(function ($value, $key) use ($request) {
            if (isset($value) && !is_null($value)) {
                $request->push([$key => $value]);
            }
        });

        return $request;
    }
}