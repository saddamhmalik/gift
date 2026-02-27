import api from './client'

export const createOrder    = (data = {})       => api.post('/order', data).then(r => r.data)
export const getOrder       = (params = {})     => api.get('/order', { params }).then(r => r.data)
export const getOrderById   = (id)              => api.get(`/order/${id}`).then(r => r.data)
export const getOrders      = (page = 1)        => api.get('/orders', { params: { page } }).then(r => r.data)

export const setOrderItem   = (data)            => api.post('/order/item', data).then(r => r.data)
export const updateOrderItem = (data)           => api.put('/order/item', data).then(r => r.data)
export const clearOrderItem = ()                => api.delete('/order/item').then(r => r.data)
