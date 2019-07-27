<?php

namespace TinyPixel\WordPress\Stripe;

use \Exception;

class StripeException extends Exception
{
    /**
     * Error message
     *
     * @param  \Stripe\Error                  $err
     * @param  string                         $label
     * @return \Illuminate\Support\Collection $error
     */
    public function errorMessage(mixed $err, string $label)
    {
        return $this->stripeException($err, $label);
    }

    /**
     * Returns Stripe Error
     *
     * @param  \Stripe\Error                  $err
     * @param  string                         $label
     * @return \Illuminate\Support\Collection $error
     */
    public function stripeException(mixed $err, string $label)
    {
        $errorBody   = $err->getJsonBody();
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
}