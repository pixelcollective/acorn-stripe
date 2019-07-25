<script src="https://js.stripe.com/v3/"></script>

<form action="/wp-json/acorn/stripe/client" method="post" id="stripe-form">
  <div class="form-row">
    <label for="card-element">
      {!! $labelText ?? 'Credit or debit card' !!}
    </label>
    <div id="card-element"></div>

    <div id="card-errors" role="alert"></div>
  </div>

  <button>{!! $buttonText ?? 'Submit payment' !!}</button>
</form>