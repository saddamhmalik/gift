import { useState } from 'react'
import { Link, useNavigate, useLocation } from 'react-router-dom'
import { Eye, EyeOff, Zap, Loader2, ArrowRight } from 'lucide-react'
import { useAuth } from '../contexts/AuthContext'

export default function LoginPage() {
  const { login, loading } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()
  const from = location.state?.from?.pathname || '/'

  const [form, setForm]     = useState({ email: '', password: '' })
  const [showPw, setShowPw] = useState(false)
  const [error, setError]   = useState('')

  const set = (k) => (e) => setForm(f => ({ ...f, [k]: e.target.value }))

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    try {
      await login(form)
      navigate(from, { replace: true })
    } catch (err) {
      setError(err.response?.data?.message || 'Invalid email or password.')
    }
  }

  return (
    <div className="min-h-screen bg-surface-950 flex items-center justify-center px-4 py-12 relative overflow-hidden">
      {/* Background blobs */}
      <div className="absolute -top-40 -left-40 w-[500px] h-[500px] rounded-full bg-primary-600/20 blur-[120px] pointer-events-none" />
      <div className="absolute -bottom-40 -right-40 w-[500px] h-[500px] rounded-full bg-brand/15 blur-[120px] pointer-events-none" />

      <div className="w-full max-w-md relative z-10">
        {/* Card */}
        <div className="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl shadow-black/40">

          {/* Logo */}
          <div className="text-center mb-8">
            <Link to="/" className="inline-flex w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-brand items-center justify-center shadow-glow-primary mb-4">
              <Zap size={22} className="text-white" />
            </Link>
            <p className="text-2xl font-extrabold text-white mb-1">
              Pay<span className="text-primary-400">Flex</span>
            </p>
            <p className="text-sm text-slate-400">Sign in to your account</p>
          </div>

          {error && (
            <div className="mb-5 bg-red-500/10 border border-red-500/30 text-red-400 text-sm px-4 py-3 rounded-xl">
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-slate-300 mb-1.5">Email address</label>
              <input
                type="email" required
                value={form.email} onChange={set('email')}
                placeholder="you@example.com"
                className="w-full bg-white/8 border border-white/12 focus:border-primary-500/60 text-white placeholder-slate-500 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-primary-500/20 transition-all"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-slate-300 mb-1.5">Password</label>
              <div className="relative">
                <input
                  type={showPw ? 'text' : 'password'} required
                  value={form.password} onChange={set('password')}
                  placeholder="••••••••"
                  className="w-full bg-white/8 border border-white/12 focus:border-primary-500/60 text-white placeholder-slate-500 rounded-xl px-4 py-2.5 pr-11 text-sm outline-none focus:ring-2 focus:ring-primary-500/20 transition-all"
                />
                <button type="button" onClick={() => setShowPw(v => !v)}
                  className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors">
                  {showPw ? <EyeOff size={16} /> : <Eye size={16} />}
                </button>
              </div>
            </div>

            <button type="submit" disabled={loading}
              className="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-violet-500 text-white font-bold text-sm transition-all shadow-md shadow-primary-900/40 disabled:opacity-50 disabled:cursor-not-allowed mt-2">
              {loading
                ? <><Loader2 size={16} className="animate-spin" /> Signing in…</>
                : <><span>Sign In</span><ArrowRight size={15} /></>}
            </button>
          </form>

          <p className="text-center text-sm text-slate-500 mt-6">
            Don&apos;t have an account?{' '}
            <Link to="/register" className="text-primary-400 font-semibold hover:text-primary-300">Create one free</Link>
          </p>
        </div>

        {/* Back home */}
        <p className="text-center mt-5">
          <Link to="/" className="text-xs text-slate-600 hover:text-slate-400 transition-colors">← Back to PayFlex</Link>
        </p>
      </div>
    </div>
  )
}
