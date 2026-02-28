import api from './client'

export const login = (data) => api.post('/auth/login', data).then((r) => r.data)
export const register = (data) => api.post('/auth/register', data).then((r) => r.data)
export const logout = () => api.post('/auth/logout').then((r) => r.data)
export const me = () => api.get('/auth/me').then((r) => r.data)
export const forgotPassword = (email) =>
  api.post('/auth/forgot-password', { email }).then((r) => r.data)
export const resetPassword = (data) => api.post('/auth/reset-password', data).then((r) => r.data)
