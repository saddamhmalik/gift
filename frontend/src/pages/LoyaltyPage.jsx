import { Star, Zap, Gift, RefreshCw, Clock, CreditCard, TrendingUp, Briefcase } from 'lucide-react'
import { Link } from 'react-router-dom'

const TIERS = [
  { pct: '0.5%', label: 'Silver', color: 'from-gray-400 to-gray-500', bg: 'bg-gray-50 border-gray-200' },
  { pct: '1.0%', label: 'Gold',   color: 'from-amber-400 to-yellow-500', bg: 'bg-amber-50 border-amber-200' },
  { pct: '1.5%', label: 'Platinum', color: 'from-blue-400 to-indigo-500', bg: 'bg-blue-50 border-blue-200' },
  { pct: '2.0%', label: 'Elite', color: 'from-purple-500 to-violet-600', bg: 'bg-purple-50 border-purple-200' },
]

const HOW_EARN = [
  { step: '01', title: 'Sign In or Register', body: 'Visit payflex.in and log into your account or create a new one in seconds.' },
  { step: '02', title: 'Choose a Product', body: 'Browse thousands of gift cards and vouchers from leading brands across India.' },
  { step: '03', title: 'Complete Payment', body: 'Pay using any eligible mode — Debit/Credit Card, UPI, Net Banking, or supported Wallets.' },
  { step: '04', title: 'Points Credited Instantly', body: 'PayFlex Points land in your account the moment your transaction is successfully completed.' },
]

const ELIGIBLE_MODES = [
  { Icon: CreditCard, label: 'Debit Card' },
  { Icon: CreditCard, label: 'Credit Card' },
  { Icon: Zap,        label: 'UPI' },
  { Icon: TrendingUp, label: 'Net Banking' },
  { Icon: Star,       label: 'Wallets' },
]

const RULES = [
  'PayFlex Points are valid for 30 days from the date of credit.',
  'PayFlex Points cannot be revalidated once expired.',
  'Points will be credited immediately after a successful transaction.',
  'PayFlex Points cannot be earned on transactions paid using PayFlex Points themselves.',
  'The PayFlex Loyalty Program is a property of PayFlex Pvt. Ltd. Terms are subject to change.',
  'Earn points on eligible modes only: Debit Card, Credit Card, UPI, Net Banking, and supported Wallets.',
  'Check the exact PayFlex Points to be earned on each product page before purchasing.',
]

export default function LoyaltyPage() {
  return (
    <div className="min-h-screen">
      {/* Hero */}
      <section className="relative bg-gradient-to-br from-violet-600 via-purple-600 to-primary-600 text-white overflow-hidden">
        <div className="absolute inset-0 opacity-10">
          {Array.from({ length: 15 }).map((_, i) => (
            <Star key={i} className="absolute text-white"
              style={{ width: Math.random() * 30 + 10, top: `${Math.random() * 100}%`, left: `${Math.random() * 100}%`, opacity: Math.random() }} />
          ))}
        </div>
        <div className="relative max-w-4xl mx-auto px-6 py-24 text-center">
          <div className="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm text-white text-xs font-bold px-4 py-1.5 rounded-full mb-6">
            <Star size={12} fill="currentColor" /> PayFlex Loyalty Program
          </div>
          <h1 className="text-4xl sm:text-5xl font-extrabold leading-tight mb-4">
            Earn Every Time<br />You Transact
          </h1>
          <p className="text-lg text-white/85 max-w-xl mx-auto mb-8">
            Earn up to <strong>2% PayFlex Points</strong> on every eligible payment. 1 PayFlex Point = ₹1. Use your points on your next purchase and save more, every time.
          </p>
          <div className="flex flex-col sm:flex-row gap-3 justify-center">
            <Link to="/register" className="bg-white text-purple-700 font-bold px-8 py-3 rounded-xl hover:bg-gray-50 transition-colors">
              Join Now — It's Free
            </Link>
            <Link to="/categories" className="border border-white/40 text-white font-semibold px-8 py-3 rounded-xl hover:bg-white/10 transition-colors">
              Browse Gift Cards
            </Link>
          </div>
        </div>
      </section>

      {/* Key stat */}
      <section className="bg-white border-b border-gray-100">
        <div className="max-w-4xl mx-auto px-6 py-12 grid grid-cols-2 sm:grid-cols-4 gap-6 text-center">
          {[['1 Point', '= ₹1 Value'], ['Up to 2%', 'Back on spends'], ['30 Days', 'Points validity'], ['Instant', 'Credit after payment']].map(([v, l]) => (
            <div key={l}>
              <p className="text-2xl font-extrabold text-purple-600">{v}</p>
              <p className="text-xs text-gray-500 mt-1">{l}</p>
            </div>
          ))}
        </div>
      </section>

      {/* Reward tiers */}
      <section className="max-w-4xl mx-auto px-6 py-20">
        <div className="text-center mb-12">
          <span className="text-xs font-bold uppercase tracking-widest text-purple-500 mb-3 block">Reward Tiers</span>
          <h2 className="text-3xl font-extrabold text-gray-900">How much can you earn?</h2>
          <p className="text-sm text-gray-500 mt-2">Earn between 0.5% and 2% based on your transaction. Check each product page for the exact rate.</p>
        </div>
        <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
          {TIERS.map(({ pct, label, color, bg }) => (
            <div key={label} className={`rounded-2xl border p-5 text-center ${bg}`}>
              <div className={`w-12 h-12 rounded-xl bg-gradient-to-br ${color} flex items-center justify-center mx-auto mb-3 shadow-sm`}>
                <Star size={22} className="text-white" fill="white" />
              </div>
              <p className="text-2xl font-extrabold text-gray-800">{pct}</p>
              <p className="text-xs text-gray-500 mt-1 font-medium">{label}</p>
            </div>
          ))}
        </div>
      </section>

      {/* How to earn */}
      <section className="bg-gray-50 py-20">
        <div className="max-w-4xl mx-auto px-6">
          <div className="text-center mb-12">
            <span className="text-xs font-bold uppercase tracking-widest text-purple-500 mb-3 block">How It Works</span>
            <h2 className="text-3xl font-extrabold text-gray-900">Earn PayFlex Points in 4 steps</h2>
          </div>
          <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {HOW_EARN.map(({ step, title, body }) => (
              <div key={step} className="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p className="text-3xl font-black text-purple-100 mb-2">{step}</p>
                <h3 className="font-bold text-gray-800 mb-1.5 text-sm">{title}</h3>
                <p className="text-xs text-gray-500 leading-relaxed">{body}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* How to redeem */}
      <section className="max-w-4xl mx-auto px-6 py-20">
        <div className="grid md:grid-cols-2 gap-12 items-start">
          <div>
            <span className="text-xs font-bold uppercase tracking-widest text-purple-500 mb-3 block">Redeeming Points</span>
            <h2 className="text-3xl font-extrabold text-gray-900 mb-4">Use your points, save instantly</h2>
            <p className="text-sm text-gray-600 leading-relaxed mb-6">
              Check your PayFlex Points balance by logging into your account. On your next eligible transaction, apply your points at checkout to reduce the payable amount. No minimums, no fuss.
            </p>
            <div className="space-y-3">
              {[
                [RefreshCw, 'Check balance in your account dashboard'],
                [Gift,      'Apply points at checkout on any eligible order'],
                [Zap,       'Discount applied instantly to your payment'],
              ].map(([Icon, text]) => (
                <div key={text} className="flex items-start gap-3 text-sm text-gray-700">
                  <div className="w-7 h-7 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0">
                    <Icon size={14} className="text-purple-500" />
                  </div>
                  {text}
                </div>
              ))}
            </div>
          </div>

          <div>
            <span className="text-xs font-bold uppercase tracking-widest text-purple-500 mb-3 block">Special Occasions</span>
            <div className="space-y-4">
              {[
                [Star,      'Birthday & Anniversaries', 'Celebrate life\'s moments — earn points while gifting for any special occasion.'],
                [Briefcase, 'Corporate & Bulk Orders', 'Place bulk payments for events or client gifting and earn significant points to redeem later.'],
                [Gift,      'Festivals & Holidays',    'From Diwali to New Year — PayFlex rewards you every time, all year round.'],
              ].map(([Icon, title, body]) => (
                <div key={title} className="flex gap-4 bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                  <div className="w-9 h-9 rounded-xl bg-purple-50 flex items-center justify-center flex-shrink-0">
                    <Icon size={16} className="text-purple-500" />
                  </div>
                  <div>
                    <p className="font-semibold text-gray-800 text-sm">{title}</p>
                    <p className="text-xs text-gray-500 mt-0.5 leading-relaxed">{body}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Eligible payment modes */}
      <section className="bg-gray-50 py-14">
        <div className="max-w-3xl mx-auto px-6 text-center">
          <h3 className="text-lg font-bold text-gray-800 mb-6">Eligible Payment Modes</h3>
          <div className="flex flex-wrap justify-center gap-3">
            {['Debit Card', 'Credit Card', 'UPI', 'Net Banking', 'Supported Wallets'].map(mode => (
              <span key={mode} className="bg-white border border-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-xl shadow-sm">
                {mode}
              </span>
            ))}
          </div>
          <p className="text-xs text-gray-400 mt-4">Points cannot be earned on transactions paid using PayFlex Points themselves.</p>
        </div>
      </section>

      {/* T&C */}
      <section className="max-w-3xl mx-auto px-6 py-16">
        <h3 className="text-lg font-bold text-gray-800 mb-5 flex items-center gap-2">
          <Clock size={16} className="text-gray-400" /> Program Terms
        </h3>
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm divide-y divide-gray-50">
          {RULES.map((rule, i) => (
            <div key={i} className="flex items-start gap-3 px-5 py-3.5 text-sm text-gray-600">
              <span className="w-5 h-5 rounded-full bg-purple-50 text-purple-500 flex items-center justify-center flex-shrink-0 text-xs font-bold mt-0.5">{i + 1}</span>
              {rule}
            </div>
          ))}
        </div>
      </section>

      {/* CTA */}
      <section className="bg-gradient-to-br from-purple-600 to-primary-600 py-16 text-center text-white">
        <h2 className="text-2xl font-extrabold mb-3">Start Earning Today</h2>
        <p className="text-white/80 text-sm mb-8">Every transaction is a step toward your next reward. Join PayFlex now.</p>
        <Link to="/register" className="bg-white text-purple-700 font-bold px-8 py-3 rounded-xl hover:bg-gray-50 transition-colors inline-block">
          Create Free Account
        </Link>
      </section>
    </div>
  )
}
