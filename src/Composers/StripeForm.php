<?php

namespace App\Composers;

use Roots\Acorn\View\Composer;

/**
 * Stripe View Composer
 *
 */
class StripeForm extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'vendor.stripe',
        'forms.stripe',
    ];

    /**
     * Dollar amount for form
     *
     * @var int $value
     */
    public $value = 25;

    /**
     * Data to be passed to view before rendering.
     *
     * @param  array $data
     * @param  \Illuminate\View\View $view
     * @return array
     */
    public function with($data, $view)
    {
        return [
            'inputLabel' => $this->inputLabel(),
            'buttonText' => $this->buttonText(),
            'value'      => $this->value(),
        ];
    }

    /**
     * Input label
     *
     * @return str
     */
    public function inputLabel()
    {
        return 'Payment information';
    }

    /**
     * Button text
     *
     * @return str
     */
    public function buttonText()
    {
        $amt = $this->value();

        return "Pay {$amt}";
    }

    /**
     * Amount to charge
     *
     * @return int
     */
    public function value()
    {
        return $this->value;
    }
}
