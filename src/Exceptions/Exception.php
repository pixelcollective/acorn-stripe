<?php

namespace TinyPixel\WordPress\Stripe;

use \Exception as BaseException;

class Exception extends BaseException
{
    public function errorMessage($errorMessage) {
        return $errorMessage;
    }
}