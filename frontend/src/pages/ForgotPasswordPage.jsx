import { useState } from 'react'
import { Link } from 'react-router-dom'
import { Zap, Loader2, ArrowRight, CheckCircle } from 'lucide-react'
import { forgotPassword } from '../api/auth'

export default function ForgotPasswordPage() {
  const [email, setEmail] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const [sent, setSent] = useState(false)

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    setLoading(true)
    try {
      await forgotPassword(email)
      setSent(true)
    } catch (err) {
      setError(err.response?.data?.message || 'Something went wrong. Try again.')
    } finally {
      setLoading(false)
    }
  }

  if (sent) {
    return (
      <div className="min-h-screen bg-surface-950 flex items-center justify-center px-4 py-12 relative overflow-hidden">
        <div className="absolute -top-40 -left-40 w-[500px] h-[500px] rounded-full bg-primary-600/20 blur-[120px] pointer-events-none" />
        <div className="absolute -bottom-40 -right-40 w-[500px] h-[500px] rounded-full bg-brand/15 blur-[120px] pointer-events-none" />
        <div className="w-full max-w-md relative z-10">
          <div className="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl shadow-black/40 text-center">
            <div className="inline-flex w-14 h-14 rounded-2xl bg-primary-500/20 items-center justify-center mb-4">
              <CheckCircle size={28} className="text-primary-400" />
            </div>
            <h1 className="text-xl font-bold text-white mb-2">Check your email</h1>
            <p className="text-slate-400 text-sm mb-6">
              If an account exists for <span className="text-slate-300 font-medium">{email}</span>,
              we sent a password reset link. It may take a few minutes. Check spam if you don&apos;t
              see it.
            </p>
            <Link
              to="/login"
              className="inline-flex items-center gap-2 py-2.5 px-4 rounded-xl bg-white/10 hover:bg-white/15 text-white text-sm font-semibold transition-colors"
            >
              Back to sign in <ArrowRight size={14} />
            </Link>
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
            <p className="text-sm text-slate-400">Reset your password</p>
          </div>
          {error && (
            <div className="mb-5 bg-red-500/10 border border-red-500/30 text-red-400 text-sm px-4 py-3 rounded-xl">
              {error}
            </div>
          )}
          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-white mb-1.5">
                Email address
              </label>
              <input
                type="email"
                required
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="you@example.com"
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
                  <Loader2 size={16} className="animate-spin" /> Sending…
                </>
              ) : (
                <>
                  <span>Send reset link</span>
                  <ArrowRight size={15} />
                </>
              )}
            </button>
          </form>
          <p className="text-center text-sm text-slate-500 mt-6">
            Remember your password?{' '}
            <Link to="/login" className="text-primary-400 font-semibold hover:text-primary-300">
              Sign in
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
