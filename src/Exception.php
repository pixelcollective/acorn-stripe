<?php

namespace TinyPixel\WordPress\Stripe;

use Exception as BaseException;

class Exception extends BaseException
{
    public function errorMessage() {
        //error message
        $errorMsg = 'Error on line '.$this->getLine().' in '.$this->getFile()
        .': <b>'.$this->getMessage().'</b> is not a valid E-Mail address';

        return $errorMsg;
    }
}