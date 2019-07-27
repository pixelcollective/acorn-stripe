<?php

namespace TinyPixel\WordPress\Stripe\Traits;

use \Illuminate\Support\Collection;

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

}