import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { Star, TrendingUp, Clock, Gift, ChevronRight, Loader2, Zap, RefreshCw } from 'lucide-react'
import { getLoyaltyBalance, getLoyaltyHistory } from '../api/loyalty'
import { useAuth } from '../contexts/AuthContext'

/* ─── Helpers ── */
function StatCard({ label, value, sub, color = 'text-gray-900', bg = 'bg-white' }) {
  return (
    <div className={`${bg} rounded-2xl border border-gray-100 p-5 shadow-sm`}>
      <p className="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">{label}</p>
      <p className={`text-2xl font-extrabold ${color}`}>{value}</p>
      {sub && <p className="text-xs text-gray-400 mt-0.5">{sub}</p>}
    </div>
  )
}

function TransactionRow({ tx }) {
  const isCredit  = tx.type === 'credit'
  const expired   = tx.is_expired
  return (
    <div className={`flex items-center gap-4 py-4 border-b border-gray-50 last:border-0 ${expired ? 'opacity-50' : ''}`}>
      <div className={`w-9 h-9 rounded-full flex items-center justify-center shrink-0 ${isCredit ? 'bg-emerald-100' : 'bg-red-100'}`}>
        {isCredit
          ? <Star size={15} className="text-emerald-600" fill="currentColor" />
          : <Gift size={15} className="text-red-500" />
        }
      </div>
      <div className="flex-1 min-w-0">
        <p className="text-sm font-semibold text-gray-800 truncate">{tx.description}</p>
        <p className="text-xs text-gray-400 mt-0.5">
          {new Date(tx.created_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' })}
          {tx.expires_at && isCredit && !expired && (
            <span className="ml-2 text-amber-500">· Expires {new Date(tx.expires_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'short' })}</span>
          )}
          {expired && <span className="ml-2 text-red-400">· Expired</span>}
        </p>
      </div>
      <p className={`text-sm font-extrabold shrink-0 ${isCredit ? 'text-emerald-600' : 'text-red-500'}`}>
        {isCredit ? '+' : '−'}{Math.abs(tx.points).toFixed(0)} pts
      </p>
    </div>
  )
}

/* ─── How it works steps ── */
const HOW_IT_WORKS = [
  { Icon: Zap,       title: 'Complete a Purchase', body: 'Buy any gift card or voucher using PayU with an eligible payment method.' },
  { Icon: Star,      title: 'Earn Points Instantly', body: 'Points are credited immediately after a successful transaction. 1 pt = ₹1.' },
  { Icon: Gift,      title: 'Apply at Checkout', body: 'Use your points on your next purchase to reduce the amount payable.' },
  { Icon: RefreshCw, title: 'Valid for 30 Days', body: 'Points expire 30 days from credit. Keep transacting to stay active.' },
]

const TIERS = [
  { label: 'Silver',   pct: '0.5%', color: 'from-gray-400 to-gray-500',     ring: 'ring-gray-300'   },
  { label: 'Gold',     pct: '1.0%', color: 'from-amber-400 to-yellow-500',   ring: 'ring-amber-300'  },
  { label: 'Platinum', pct: '1.5%', color: 'from-blue-400 to-indigo-500',    ring: 'ring-blue-300'   },
  { label: 'Elite',    pct: '2.0%', color: 'from-purple-500 to-violet-600',  ring: 'ring-purple-300' },
]

export default function MyPointsPage() {
  const { user } = useAuth()
  const [historyPage, setHistoryPage] = useState(1)

  const { data: balanceData, isLoading: balanceLoading } = useQuery({
    queryKey:  ['loyalty', 'balance'],
    queryFn:   getLoyaltyBalance,
    staleTime: 1000 * 60,
  })

  const { data: historyData, isLoading: historyLoading } = useQuery({
    queryKey:  ['loyalty', 'history', historyPage],
    queryFn:   () => getLoyaltyHistory(historyPage, 10),
    staleTime: 1000 * 30,
  })

  if (!user) return (
    <div className="min-h-[60vh] flex flex-col items-center justify-center text-center p-8">
      <Star size={48} className="text-amber-400 mb-4" />
      <h2 className="text-xl font-bold text-gray-800 mb-2">Sign in to view your points</h2>
      <p className="text-gray-500 mb-6">Earn PayFlex Points on every purchase and use them to save more.</p>
      <Link to="/login" className="btn-primary">Login / Register</Link>
    </div>
  )

  const info     = balanceData?.data
  const history  = historyData?.data
  const meta     = history?.meta
  const txList   = history?.data ?? []

  return (
    <div className="min-h-screen">

      {/* ── Hero ── */}
      <section className="relative bg-gradient-to-br from-violet-600 via-purple-600 to-primary-600 text-white overflow-hidden">
        <div className="absolute inset-0 opacity-10">
          {Array.from({ length: 12 }).map((_, i) => (
            <Star key={i} className="absolute text-white"
              style={{ width: Math.random() * 28 + 8, top: `${Math.random() * 100}%`, left: `${Math.random() * 100}%`, opacity: Math.random() }} />
          ))}
        </div>
        <div className="relative max-w-3xl mx-auto px-6 py-16 text-center">
          <div className="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm text-white text-xs font-bold px-4 py-1.5 rounded-full mb-5">
            <Star size={12} fill="currentColor" /> PayFlex Loyalty Program
          </div>
          <h1 className="text-3xl sm:text-4xl font-extrabold leading-tight mb-2">My PayFlex Points</h1>
          {balanceLoading ? (
            <Loader2 size={28} className="animate-spin mx-auto mt-4" />
          ) : (
            <div className="mt-4">
              <p className="text-6xl font-black tracking-tight">
                {info ? info.balance.toFixed(0) : '—'}
              </p>
              <p className="text-white/80 mt-1 text-sm">Available Points · 1 pt = ₹1</p>
              {info?.expiring_soon > 0 && (
                <p className="mt-3 inline-flex items-center gap-1.5 bg-amber-400/20 border border-amber-400/40 text-amber-200 text-xs font-semibold px-3 py-1 rounded-full">
                  <Clock size={12} /> {info.expiring_soon.toFixed(0)} pts expiring in 7 days!
                </p>
              )}
            </div>
          )}
        </div>
      </section>

      <div className="max-w-4xl mx-auto px-4 sm:px-6 py-10 space-y-10">

        {/* ── Stats ── */}
        {info && (
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <StatCard label="Available"      value={info.balance.toFixed(0)}           sub="Points"       color="text-violet-600" />
            <StatCard label="Expiring Soon"  value={info.expiring_soon.toFixed(0)}     sub="In 7 days"    color="text-amber-500"  />
            <StatCard label="Lifetime Earned" value={info.lifetime_earned.toFixed(0)}  sub="Total pts"    color="text-emerald-600" />
            <StatCard label="Total Redeemed"  value={info.lifetime_redeemed.toFixed(0)} sub="Points used" color="text-gray-700"   />
          </div>
        )}

        {/* ── Points value callout ── */}
        {info && info.balance > 0 && (
          <div className="flex items-center gap-4 bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4">
            <div className="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
              <Star size={22} className="text-amber-500" fill="currentColor" />
            </div>
            <div>
              <p className="font-bold text-gray-800">
                Your points are worth <span className="text-amber-600">₹{info.balance.toFixed(0)}</span>!
              </p>
              <p className="text-xs text-gray-500 mt-0.5">
                Apply them on your next purchase to save instantly.{' '}
                <Link to="/" className="text-amber-600 font-semibold underline">Shop now →</Link>
              </p>
            </div>
          </div>
        )}

        {/* ── Earn Rate Tiers ── */}
        <section>
          <h2 className="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <TrendingUp size={18} className="text-primary-500" /> Earn Rates
          </h2>
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
            {TIERS.map(({ label, pct, color, ring }) => (
              <div key={label} className={`bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center ring-1 ${ring}`}>
                <div className={`w-10 h-10 rounded-xl bg-gradient-to-br ${color} flex items-center justify-center mx-auto mb-2 shadow-sm`}>
                  <Star size={18} className="text-white" fill="white" />
                </div>
                <p className="text-xl font-extrabold text-gray-800">{pct}</p>
                <p className="text-xs text-gray-500 font-medium">{label}</p>
              </div>
            ))}
          </div>
          <p className="text-xs text-gray-400 mt-2 text-center">Check each product page for the exact earn rate. Default: 1%.</p>
        </section>

        {/* ── How it works ── */}
        <section>
          <h2 className="text-lg font-bold text-gray-900 mb-4">How It Works</h2>
          <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {HOW_IT_WORKS.map(({ Icon, title, body }, i) => (
              <div key={title} className="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p className="text-3xl font-black text-purple-100 mb-2">{String(i + 1).padStart(2, '0')}</p>
                <div className="w-8 h-8 rounded-lg bg-primary-50 flex items-center justify-center mb-3">
                  <Icon size={15} className="text-primary-500" />
                </div>
                <h3 className="font-bold text-gray-800 text-sm mb-1">{title}</h3>
                <p className="text-xs text-gray-500 leading-relaxed">{body}</p>
              </div>
            ))}
          </div>
        </section>

        {/* ── Transaction History ── */}
        <section>
          <h2 className="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <Clock size={18} className="text-gray-400" /> Transaction History
          </h2>
          <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            {historyLoading ? (
              <div className="flex items-center justify-center py-12">
                <Loader2 size={28} className="animate-spin text-primary-400" />
              </div>
            ) : txList.length === 0 ? (
              <div className="text-center py-12 text-gray-400">
                <Star size={32} className="mx-auto mb-3 text-gray-200" />
                <p className="text-sm font-medium">No transactions yet</p>
                <p className="text-xs mt-1">Make your first purchase to start earning points.</p>
                <Link to="/" className="inline-block mt-4 text-xs text-primary-500 font-semibold hover:underline">Browse Gift Cards →</Link>
              </div>
            ) : (
              <div className="divide-y divide-gray-50 px-5">
                {txList.map(tx => <TransactionRow key={tx.id} tx={tx} />)}
              </div>
            )}

            {/* Pagination */}
            {meta && meta.last_page > 1 && (
              <div className="flex items-center justify-between border-t border-gray-100 px-5 py-3">
                <p className="text-xs text-gray-400">Page {meta.current_page} of {meta.last_page}</p>
                <div className="flex gap-2">
                  <button
                    onClick={() => setHistoryPage(p => Math.max(1, p - 1))}
                    disabled={meta.current_page === 1}
                    className="px-3 py-1.5 text-xs font-medium border border-gray-200 rounded-lg disabled:opacity-40 hover:bg-gray-50 transition-colors"
                  >← Prev</button>
                  <button
                    onClick={() => setHistoryPage(p => Math.min(meta.last_page, p + 1))}
                    disabled={meta.current_page === meta.last_page}
                    className="px-3 py-1.5 text-xs font-medium border border-gray-200 rounded-lg disabled:opacity-40 hover:bg-gray-50 transition-colors"
                  >Next →</button>
                </div>
              </div>
            )}
          </div>
        </section>

        {/* ── T&C ── */}
        <section>
          <h2 className="text-sm font-bold text-gray-700 mb-3 flex items-center gap-1.5">
            <Clock size={14} className="text-gray-400" /> Program Terms &amp; Conditions
          </h2>
          <div className="bg-white rounded-2xl border border-gray-100 shadow-sm divide-y divide-gray-50">
            {[
              'A customer can earn Reward Points (PayFlex Points) in the range of 0.5%, 1.0%, 1.5%, and 2.0% based on the amount paid for any transaction.',
              'A customer can check the exact PayFlex Points to be earned on the respective product or service page.',
              'PayFlex Points are valid for 30 days from the date of credit.',
              'PayFlex Points cannot be revalidated once expired.',
              'PayFlex Points will be credited to your PayFlex account immediately after the transaction is successfully completed.',
              'PayFlex Points can be earned on payments made via Debit Card, Credit Card, UPI, Net Banking, and supported wallets.',
              'PayFlex Points cannot be earned on transactions paid using PayFlex Points themselves.',
              'PayFlex Points must be used before their validity date. Expired points cannot be revalidated.',
              'The PayFlex Loyalty Program is a property of PayFlex Pvt. Ltd. Terms & conditions are subject to change as per https://payflex.in/ guidelines.',
            ].map((rule, i) => (
              <div key={i} className="flex items-start gap-3 px-5 py-3 text-xs text-gray-500">
                <span className="w-5 h-5 rounded-full bg-purple-50 text-purple-500 flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">{i + 1}</span>
                {rule}
              </div>
            ))}
          </div>
          <p className="text-[10px] text-gray-300 text-center mt-3">
            For the full program details visit <span className="text-primary-400">https://payflex.in/loyalty</span>
          </p>
        </section>

      </div>
    </div>
  )
}
