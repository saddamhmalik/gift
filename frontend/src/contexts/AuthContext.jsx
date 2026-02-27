import { createContext, useContext, useState, useEffect, useCallback } from 'react'
import { login as loginApi, register as registerApi, logout as logoutApi, me as meApi } from '../api/auth'

const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const [user, setUser]       = useState(() => {
    try { return JSON.parse(localStorage.getItem('auth_user')) } catch { return null }
  })
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    const token = localStorage.getItem('auth_token')
    if (token && !user) {
      meApi().then(r => setUser(r.data)).catch(() => {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_user')
      })
    }
  }, []) // eslint-disable-line

  useEffect(() => {
    const handleLogout = () => { setUser(null) }
    window.addEventListener('auth:logout', handleLogout)
    return () => window.removeEventListener('auth:logout', handleLogout)
  }, [])

  const login = useCallback(async (credentials) => {
    setLoading(true)
    try {
      const res = await loginApi(credentials)
      localStorage.setItem('auth_token', res.data.token)
      localStorage.setItem('auth_user', JSON.stringify(res.data.user))
      setUser(res.data.user)
      return res
    } finally { setLoading(false) }
  }, [])

  const register = useCallback(async (data) => {
    setLoading(true)
    try {
      const res = await registerApi(data)
      localStorage.setItem('auth_token', res.data.token)
      localStorage.setItem('auth_user', JSON.stringify(res.data.user))
      setUser(res.data.user)
      return res
    } finally { setLoading(false) }
  }, [])

  const logoutUser = useCallback(async () => {
    setLoading(true)
    try { await logoutApi() } catch { /* ignore */ } finally {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      setUser(null)
      setLoading(false)
    }
  }, [])

  return (
    <AuthContext.Provider value={{ user, loading, login, register, logout: logoutUser }}>
      {children}
    </AuthContext.Provider>
  )
}

export const useAuth = () => {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth must be used inside AuthProvider')
  return ctx
}
