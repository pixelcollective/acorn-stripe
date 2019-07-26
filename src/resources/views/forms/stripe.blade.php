<form action="/wp-json/tiny-pixel/stripe" method="post" id="payment-form" class="stripe-form">
  <div class="form-row">
    <label for="card-element">
      {!! $inputLabel !!}
    </label>

    <div id="card-element"></div>
    <div id="card-errors" role="alert"></div>
  </div>

  <input type="hidden" value="{!! $value !!}" name="amount" />
  <button class="stripe-form-payment-submit">{!! $buttonText !!}</button>
</form>