import axios from 'axios'

const stripeTokenHandler = token => {
  const form = document.getElementById('payment-form')
  let hiddenInput = document.createElement('input')

  hiddenInput.setAttribute('type', 'hidden')
  hiddenInput.setAttribute('name', 'stripeToken')
  hiddenInput.setAttribute('value', token.id)

  form.appendChild(hiddenInput)
  form.submit()
}

const getClientId = () => {
  return axios.get('/wp-json/acorn/stripe/client').then(res =>
    res.clientId
  )
}

export {
  stripeTokenHandler,
  getClientId,
}