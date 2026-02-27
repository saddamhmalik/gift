import api from './client'

export const initiatePayment = (data) => api.post('/payment/initiate', data).then(r => r.data)

/**
 * Programmatically submits a hidden HTML form to PayU.
 * PayU requires a browser POST (not fetch/XHR) to redirect the user to their payment page.
 */
export function redirectToPayU(payuParams) {
  // Remove our meta field — only send PayU params
  const { payu_url, ...fields } = payuParams

  const form = document.createElement('form')
  form.method = 'POST'
  form.action = payu_url

  Object.entries(fields).forEach(([key, value]) => {
    const input = document.createElement('input')
    input.type  = 'hidden'
    input.name  = key
    input.value = value ?? ''
    form.appendChild(input)
  })

  document.body.appendChild(form)
  form.submit()
}
