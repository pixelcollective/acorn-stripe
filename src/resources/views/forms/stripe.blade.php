<form id="stripe-form" class="stripe-form">
  <div class="form-row">
    <label class="cc-label" for="card-element">
      {!! $inputLabel !!}
    </label>

    <div id="card-element"></div>
    <div id="card-errors" role="alert"></div>
    <div id="payment-success" role="alert"></div>
  </div>

  <input type="hidden" class="stripe-transaction-amount" value="{!! $value !!}" name="amount" />
  <button class="stripe-form-payment-submit">{!! $buttonText !!}</button>
</form>