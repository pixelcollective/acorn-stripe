import axios from 'axios'

/**
 * Get Stripe token
 */
axios.get(`/wp-json/tiny-pixel/stripe`).then(
  res => doForm(res.data.clientId)
)

/**
 * Handle form
 */
const doForm = clientId => {
  /**
   * Stripe
   */
  const stripe = Stripe(clientId)
  const elements = stripe.elements()

  /**
   * JSS ✨
   */
  const style = {
    base: {
      color: `#32325d`,
      fontFamily: `"Helvetica Neue", Helvetica, sans-serif`,
      fontSmoothing: `antialiased`,
      fontSize: `16px`,
      '::placeholder': {
        color: `#aab7c4`
      }
    },
    invalid: {
      color: `#fa755a`,
      iconColor: `#fa755a`
    }
  }

  /**
   * Card element
   */
  const card = elements.create(`card`, { style })

  card.mount(`#card-element`)
  card.addEventListener(`change`, function (event) {
    const displayError = document.getElementById(`card-errors`)

    if (event.error) {
      displayError.textContent = event.error.message
    } else {
      displayError.textContent = ``
    }
  })

  /**
   * Form
   */
  const form = document.getElementById(`stripe-form`)

  form && form.addEventListener(`submit`, e => {
    e.preventDefault()

    stripe.createToken(card).then(result => {
      if (result.error) {
        const errorElement = document.getElementById(`card-errors`)

        if (errorElement) {
          errorElement.textContent = result.error.message
        }
      } else {
        stripeTokenHandler(result.token)
      }
    })
  })

  /**
   * Process with Stripe and WP API
   */
  const stripeTokenHandler = stripeToken => {
    const form = document.getElementById(`stripe-form`)
    const amount = form.querySelector(`.stripe-transaction-amount`).value
    const token = stripeToken.id

    form && amount && axios.post(`/wp-json/tiny-pixel/stripe`, {amount, token})
    .then(res => interfaceSuccess())
    .catch(err => console.log(err))
  }

  const interfaceSuccess = () => {
    const successElement = document.getElementById(`payment-success`)
    const allOtherFormElements = form.querySelectorAll([
      `#card-element`,
      `#card-errors`,
      `.cc-label`,
      `.stripe-form-payment-submit`,
    ])

    successElement && (() => {
      successElement.textContent = `Payment successful. Thank you!`
    })

    allOtherFormElements && allOtherFormElements.forEach(element => {
      element.style.display = `none`
    })
  }
}
