import api from './client'

/**
 * Check the balance of a Woohoo gift card.
 * Public — no auth token required.
 */
export const checkCardBalance = ({ cardNumber, pin, sku }) => {
  const payload = { card_number: cardNumber }
  if (pin?.trim()) payload.pin = pin.trim()
  if (sku?.trim()) payload.sku = sku.trim()
  return api.post('/balance', payload).then((r) => r.data)
}
