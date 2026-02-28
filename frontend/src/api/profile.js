import api from './client'

export const updateProfile = (data) => api.put('/profile', data).then((r) => r.data)
export const uploadAvatar = (file) => {
  const fd = new FormData()
  fd.append('avatar', file)
  return api
    .post('/profile/avatar', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    .then((r) => r.data)
}
export const removeAvatar = () => api.delete('/profile/avatar').then((r) => r.data)
export const requestEmailChange = (email) =>
  api.post('/profile/email', { email }).then((r) => r.data)
export const verifyEmailChange = (email, otp) =>
  api.post('/profile/email/verify', { email, otp }).then((r) => r.data)
/** Public verify by OTP (no auth). */
export const verifyEmailWithOtp = (email, otp) =>
  api.post('/auth/verify-email', { email, otp }).then((r) => r.data)
export const resendEmailVerification = () => api.post('/profile/email/resend').then((r) => r.data)
export const requestPhoneChange = (phone) =>
  api.post('/profile/phone', { phone }).then((r) => r.data)
export const verifyPhoneChange = (otp) =>
  api.post('/profile/phone/verify', { otp }).then((r) => r.data)
export const changePassword = (data) => api.post('/profile/password', data).then((r) => r.data)
