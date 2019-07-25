import { stripeTokenHandler, getClientId } from './stripe'

const clientId = getClientId()
const form = document.getElementById('payment-form')

if (form) {
  const stripe = Stripe(clientId)
  let card = stripe.elements().create('card')

  card.mount('#card-element')

  card.addEventListener('change', event => {
    let displayError = document.getElementById('card-errors')

    if (event.error) {
      displayError.textContent = event.error.message
    } else {
      displayError.textContent = ''
    }
  })

  form.addEventListener('submit', e => {
    e.preventDefault()

    stripe.createToken(card).then(result => {
      if (result.error) {
        let errorElement = document.getElementById('card-errors')
        errorElement.textContent = result.error.message
      } else {
        stripeTokenHandler(result.token)
      }
    })
  })
}