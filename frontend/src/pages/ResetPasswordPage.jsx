import { useState, useEffect } from 'react'
import { Link, useNavigate, useSearchParams } from 'react-router-dom'
import { Zap, Loader2, ArrowRight, Eye, EyeOff } from 'lucide-react'
import { resetPassword } from '../api/auth'

export default function ResetPasswordPage() {
  const [searchParams] = useSearchParams()
  const navigate = useNavigate()
  const token = searchParams.get('token') || ''
  const email = searchParams.get('email') || ''

  const [password, setPassword] = useState('')
  const [passwordConfirmation, setPasswordConfirmation] = useState('')
  const [showPw, setShowPw] = useState(false)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const [success, setSuccess] = useState(false)

  const missingParams = !token || !email

  useEffect(() => {
    if (missingParams) setError('Invalid or missing reset link. Request a new password reset.')
  }, [missingParams])

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    if (password !== passwordConfirmation) {
      setError('Passwords do not match.')
      return
    }
    setLoading(true)
    try {
      await resetPassword({ email, token, password, password_confirmation: passwordConfirmation })
      setSuccess(true)
      setTimeout(() => navigate('/login', { replace: true }), 3000)
    } catch (err) {
      setError(err.response?.data?.message || 'Invalid or expired link. Request a new reset.')
    } finally {
      setLoading(false)
    }
  }

  if (success) {
    return (
      <div className="min-h-screen bg-surface-950 flex items-center justify-center px-4 py-12 relative overflow-hidden">
        <div className="absolute -top-40 -left-40 w-[500px] h-[500px] rounded-full bg-primary-600/20 blur-[120px] pointer-events-none" />
        <div className="absolute -bottom-40 -right-40 w-[500px] h-[500px] rounded-full bg-brand/15 blur-[120px] pointer-events-none" />
        <div className="w-full max-w-md relative z-10">
          <div className="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl shadow-black/40 text-center">
            <h1 className="text-xl font-bold text-white mb-2">Password updated</h1>
            <p className="text-slate-400 text-sm mb-6">
              You can now sign in with your new password. Redirecting to sign in…
            </p>
            <Link
              to="/login"
              className="inline-flex items-center gap-2 py-2.5 px-4 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 text-white text-sm font-semibold transition-colors"
            >
              Sign in <ArrowRight size={14} />
            </Link>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-surface-950 flex items-center justify-center px-4 py-12 relative overflow-hidden">
      <div className="absolute -top-40 -left-40 w-[500px] h-[500px] rounded-full bg-primary-600/20 blur-[120px] pointer-events-none" />
      <div className="absolute -bottom-40 -right-40 w-[500px] h-[500px] rounded-full bg-brand/15 blur-[120px] pointer-events-none" />

      <div className="w-full max-w-md relative z-10">
        <div className="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl shadow-black/40">
          <div className="text-center mb-8">
            <Link
              to="/"
              className="inline-flex w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-brand items-center justify-center shadow-glow-primary mb-4"
            >
              <Zap size={22} className="text-white" />
            </Link>
            <p className="text-2xl font-extrabold text-white mb-1">
              Pay<span className="text-primary-400">Flex</span>
            </p>
            <p className="text-sm text-slate-400">Choose a new password</p>
          </div>

          {error && (
            <div className="mb-5 bg-red-500/10 border border-red-500/30 text-red-400 text-sm px-4 py-3 rounded-xl">
              {error}
            </div>
          )}

          {missingParams ? (
            <div className="space-y-4">
              <p className="text-slate-400 text-sm">
                Use the link from your password reset email, or request a new one.
              </p>
              <Link
                to="/forgot-password"
                className="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-sm transition-colors"
              >
                Request reset link <ArrowRight size={14} />
              </Link>
            </div>
          ) : (
            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-white mb-1.5">
                  New password
                </label>
                <div className="relative">
                  <input
                    type={showPw ? 'text' : 'password'}
                    required
                    minLength={8}
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    placeholder="At least 8 characters"
                    className="w-full bg-slate-800/90 border border-white/12 focus:border-primary-500/60 text-white placeholder-slate-400 rounded-xl px-4 py-2.5 pr-11 text-sm outline-none focus:ring-2 focus:ring-primary-500/20 transition-all [&:-webkit-autofill]:!bg-slate-800 [&:-webkit-autofill]:!text-white"
                  />
                  <button
                    type="button"
                    onClick={() => setShowPw((v) => !v)}
                    className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors"
                  >
                    {showPw ? <EyeOff size={16} /> : <Eye size={16} />}
                  </button>
                </div>
              </div>
              <div>
                <label className="block text-sm font-medium text-white mb-1.5">
                  Confirm password
                </label>
                <input
                  type={showPw ? 'text' : 'password'}
                  required
                  minLength={8}
                  value={passwordConfirmation}
                  onChange={(e) => setPasswordConfirmation(e.target.value)}
                  placeholder="Same as above"
                  className="w-full bg-slate-800/90 border border-white/12 focus:border-primary-500/60 text-white placeholder-slate-400 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-primary-500/20 transition-all [&:-webkit-autofill]:!bg-slate-800 [&:-webkit-autofill]:!text-white"
                />
              </div>
              <button
                type="submit"
                disabled={loading}
                className="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-violet-500 text-white font-bold text-sm transition-all shadow-md shadow-primary-900/40 disabled:opacity-50 disabled:cursor-not-allowed mt-2"
              >
                {loading ? (
                  <>
                    <Loader2 size={16} className="animate-spin" /> Updating…
                  </>
                ) : (
                  <>
                    <span>Update password</span>
                    <ArrowRight size={15} />
                  </>
                )}
              </button>
            </form>
          )}

          <p className="text-center text-sm text-slate-500 mt-6">
            <Link to="/login" className="text-primary-400 font-semibold hover:text-primary-300">
              Back to sign in
            </Link>
          </p>
        </div>
        <p className="text-center mt-5">
          <Link to="/" className="text-xs text-slate-600 hover:text-slate-400 transition-colors">
            ← Back to PayFlex
          </Link>
        </p>
      </div>
    </div>
  )
}
