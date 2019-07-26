import axios from 'axios'

axios.get('/wp-json/tiny-pixel/stripe').then(
  res => doForm(res.data.clientId)
)

const doForm = clientId => {
  const stripe = Stripe(clientId)
  const elements = stripe.elements()

  const style = {
    base: {
      color: '#32325d',
      fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
      fontSmoothing: 'antialiased',
      fontSize: '16px',
      '::placeholder': {
        color: '#aab7c4'
      }
    },
    invalid: {
      color: '#fa755a',
      iconColor: '#fa755a'
    }
  }

  const card = elements.create('card', { style })
  card.mount('#card-element')

  card.addEventListener('change', function (event) {
    const displayError = document.getElementById('card-errors')
    if (event.error) {
      displayError.textContent = event.error.message
    } else {
      displayError.textContent = ''
    }
  })

  const form = document.getElementById('payment-form')
  form.addEventListener('submit', event => {
    event.preventDefault()

    stripe.createToken(card).then(result => {
      if (result.error) {
        const errorElement = document.getElementById(
          'card-errors'
        )

        if(errorElement) {
          errorElement.textContent = result.error.message
        }
      } else {
        stripeTokenHandler(result.token)
      }
    })
  })

  const stripeTokenHandler = (token) => {
    const form = document.getElementById('payment-form')
    const hiddenInput = document.createElement('input')

    hiddenInput.setAttribute('type', 'hidden')
    hiddenInput.setAttribute('name', 'stripeToken')
    hiddenInput.setAttribute('value', token.id)
    form.appendChild(hiddenInput)

    form.submit()
  }
}
