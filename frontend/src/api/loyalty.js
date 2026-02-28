import client from './client'

export const getLoyaltyBalance = () =>
  client.get('/loyalty/balance').then(r => r.data)

export const getLoyaltyHistory = (page = 1, perPage = 15) =>
  client.get('/loyalty/history', { params: { page, per_page: perPage } }).then(r => r.data)

export const estimateLoyaltyPoints = (amount) =>
  client.get('/loyalty/estimate', { params: { amount } }).then(r => r.data)
