import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { Eye, EyeOff, Zap, Loader2 } from 'lucide-react'
import { useAuth } from '../contexts/AuthContext'

export default function RegisterPage() {
  const { register, loading } = useAuth()
  const navigate = useNavigate()

  const [form, setForm]     = useState({ first_name: '', last_name: '', email: '', phone: '', password: '', password_confirmation: '' })
  const [showPw, setShowPw] = useState(false)
  const [errors, setErrors] = useState({})
  const [error, setError]   = useState('')

  const set = (k) => (e) => setForm(f => ({ ...f, [k]: e.target.value }))

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    setErrors({})
    try {
      await register(form)
      navigate('/', { replace: true })
    } catch (err) {
      const resp = err.response?.data
      if (resp?.errors) setErrors(resp.errors)
      else setError(resp?.message || 'Registration failed. Please try again.')
    }
  }

  const fieldErr = (k) => errors[k]?.[0]

  return (
    <div className="min-h-screen bg-gradient-to-br from-primary-50 via-white to-amber-50 flex items-center justify-center px-4 py-12">
      <div className="w-full max-w-md">
        <div className="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
          <div className="text-center mb-7">
            <div className="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 items-center justify-center shadow-lg mb-4">
              <Zap size={28} className="text-white" />
            </div>
            <p className="text-xl font-extrabold text-gray-900 mb-0.5">Pay<span className="text-primary-500">Flex</span></p>
            <h1 className="text-lg font-bold text-gray-800">Create account</h1>
            <p className="text-sm text-gray-500 mt-1">Join PayFlex — earn rewards on every purchase</p>
          </div>

          {error && (
            <div className="mb-4 bg-red-50 border border-red-200 text-red-600 text-sm px-4 py-3 rounded-xl">{error}</div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="grid grid-cols-2 gap-3">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1.5">First Name</label>
                <input type="text" required value={form.first_name} onChange={set('first_name')} placeholder="John"
                  className={`w-full border rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition-all ${fieldErr('first_name') ? 'border-red-300' : 'border-gray-200'}`} />
                {fieldErr('first_name') && <p className="text-xs text-red-500 mt-1">{fieldErr('first_name')}</p>}
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1.5">Last Name</label>
                <input type="text" required value={form.last_name} onChange={set('last_name')} placeholder="Doe"
                  className={`w-full border rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition-all ${fieldErr('last_name') ? 'border-red-300' : 'border-gray-200'}`} />
                {fieldErr('last_name') && <p className="text-xs text-red-500 mt-1">{fieldErr('last_name')}</p>}
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
              <input type="email" required value={form.email} onChange={set('email')} placeholder="you@example.com"
                className={`w-full border rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition-all ${fieldErr('email') ? 'border-red-300' : 'border-gray-200'}`} />
              {fieldErr('email') && <p className="text-xs text-red-500 mt-1">{fieldErr('email')}</p>}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
              <input type="tel" required value={form.phone} onChange={set('phone')} placeholder="+1 234 567 8901"
                className={`w-full border rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition-all ${fieldErr('phone') ? 'border-red-300' : 'border-gray-200'}`} />
              {fieldErr('phone') && <p className="text-xs text-red-500 mt-1">{fieldErr('phone')}</p>}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
              <div className="relative">
                <input type={showPw ? 'text' : 'password'} required value={form.password} onChange={set('password')} placeholder="Min 8 characters"
                  className={`w-full border rounded-xl px-4 py-2.5 pr-11 text-sm outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition-all ${fieldErr('password') ? 'border-red-300' : 'border-gray-200'}`} />
                <button type="button" onClick={() => setShowPw(v => !v)} className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                  {showPw ? <EyeOff size={16} /> : <Eye size={16} />}
                </button>
              </div>
              {fieldErr('password') && <p className="text-xs text-red-500 mt-1">{fieldErr('password')}</p>}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Confirm Password</label>
              <input type="password" required value={form.password_confirmation} onChange={set('password_confirmation')} placeholder="Repeat password"
                className="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent transition-all" />
            </div>

            <button type="submit" disabled={loading}
              className="btn-primary w-full !py-3 !text-base disabled:opacity-60 disabled:cursor-not-allowed">
              {loading ? <><Loader2 size={16} className="animate-spin" /> Creating account…</> : 'Create Account'}
            </button>
          </form>

          <p className="text-center text-sm text-gray-500 mt-6">
            Already have an account?{' '}
            <Link to="/login" className="text-primary-500 font-semibold hover:text-primary-600">Sign in</Link>
          </p>
        </div>
      </div>
    </div>
  )
}
