import axios from 'axios'

const api = axios.create({
  // Use relative path so Vite proxy handles it (no CORS). Falls back to localhost for direct runs.
  baseURL: import.meta.env.VITE_API_URL || '/api/v1',
  headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

api.interceptors.response.use(
  (res) => res,
  (err) => {
    if (err.response?.status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      window.dispatchEvent(new Event('auth:logout'))
    }
    return Promise.reject(err)
  }
)

export default api
