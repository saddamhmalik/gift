import { Star, Zap, Gift, RefreshCw, Clock, TrendingUp, Briefcase } from 'lucide-react'
import { Link } from 'react-router-dom'

const TIERS = [
  {
    pct: '0.5%',
    label: 'Silver',
    color: 'from-gray-400 to-gray-500',
    bg: 'bg-gray-50 border-gray-200',
  },
  {
    pct: '1.0%',
    label: 'Gold',
    color: 'from-amber-400 to-yellow-500',
    bg: 'bg-amber-50 border-amber-200',
  },
  {
    pct: '1.5%',
    label: 'Platinum',
    color: 'from-blue-400 to-indigo-500',
    bg: 'bg-blue-50 border-blue-200',
  },
  {
    pct: '2.0%',
    label: 'Elite',
    color: 'from-purple-500 to-violet-600',
    bg: 'bg-purple-50 border-purple-200',
  },
]

const HOW_EARN = [
  {
    step: '01',
    title: 'Sign In or Register',
    body: 'Visit https://payflex.in/. Login with your account credentials, or register as a new user.',
  },
  {
    step: '02',
    title: 'Choose a Product',
    body: 'Choose the product or service you want to purchase from our wide range of gift cards and vouchers.',
  },
  {
    step: '03',
    title: 'Complete Payment',
    body: 'Complete the payment using eligible modes — Debit/Credit Card, UPI, Net Banking, or supported wallets.',
  },
  {
    step: '04',
    title: 'Points Credited Instantly',
    body: 'PayFlex Points will be automatically credited to your account immediately after successful payment.',
  },
]

const RULES = [
  'A customer can earn Reward Points (PayFlex Points) in the range of 0.5%, 1.0%, 1.5%, and 2.0% based on the amount paid for any transaction.',
  'A customer can check the exact PayFlex Points to be earned on the respective product or service page.',
  'PayFlex Points are valid for 30 days from the date of credit.',
  'PayFlex Points cannot be revalidated once expired.',
  'PayFlex Points will be credited to your PayFlex account immediately after the transaction is successfully completed.',
  'PayFlex Points can be earned on payments made via Debit Card, Credit Card, UPI, Net Banking, and supported wallets.',
  'PayFlex Points cannot be earned on transactions paid using PayFlex Points themselves.',
  'The PayFlex Loyalty Program is a property of PayFlex Pvt. Ltd. Terms & conditions are subject to change as per https://payflex.in/ guidelines.',
]

export default function LoyaltyPage() {
  return (
    <div className="min-h-screen">
      {/* Hero */}
      <section className="relative bg-gradient-to-br from-violet-600 via-purple-600 to-primary-600 text-white overflow-hidden">
        <div className="absolute inset-0 opacity-10">
          {Array.from({ length: 15 }).map((_, i) => (
            <Star
              key={i}
              className="absolute text-white"
              style={{
                width: Math.random() * 30 + 10,
                top: `${Math.random() * 100}%`,
                left: `${Math.random() * 100}%`,
                opacity: Math.random(),
              }}
            />
          ))}
        </div>
        <div className="relative max-w-4xl mx-auto px-6 py-24 text-center">
          <div className="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm text-white text-xs font-bold px-4 py-1.5 rounded-full mb-6">
            <Star size={12} fill="currentColor" /> PayFlex Loyalty Program
          </div>
          <h1 className="text-4xl sm:text-5xl font-extrabold leading-tight mb-4">
            Earn Every Time
            <br />
            You Transact
          </h1>
          <p className="text-lg text-white/85 max-w-xl mx-auto mb-8">
            The PayFlex Loyalty Program gives registered users a special advantage every time they
            make a payment. Earn up to <strong>2% PayFlex Points</strong> on every eligible
            transaction. <strong>1 PayFlex Point = ₹1.</strong>
          </p>
          <div className="flex flex-col sm:flex-row gap-3 justify-center">
            <Link
              to="/register"
              className="bg-white text-purple-700 font-bold px-8 py-3 rounded-xl hover:bg-gray-50 transition-colors"
            >
              Join Now — It's Free
            </Link>
            <Link
              to="/categories"
              className="border border-white/40 text-white font-semibold px-8 py-3 rounded-xl hover:bg-white/10 transition-colors"
            >
              Browse Gift Cards
            </Link>
          </div>
        </div>
      </section>

      {/* Key stat */}
      <section className="bg-white border-b border-gray-100">
        <div className="max-w-4xl mx-auto px-6 py-12 grid grid-cols-2 sm:grid-cols-4 gap-6 text-center">
          {[
            ['1 Point', '= ₹1 Value'],
            ['0.5%–2%', 'Earn per transaction'],
            ['30 Days', 'Points validity'],
            ['Instant', 'Credit after payment'],
          ].map(([v, l]) => (
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
          <span className="text-xs font-bold uppercase tracking-widest text-purple-500 mb-3 block">
            Reward Tiers
          </span>
          <h2 className="text-3xl font-extrabold text-gray-900">How much can you earn?</h2>
          <p className="text-sm text-gray-500 mt-2">
            Earn between 0.5% and 2% based on your transaction. Check each product page for the
            exact rate.
          </p>
        </div>
        <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
          {TIERS.map(({ pct, label, color, bg }) => (
            <div key={label} className={`rounded-2xl border p-5 text-center ${bg}`}>
              <div
                className={`w-12 h-12 rounded-xl bg-gradient-to-br ${color} flex items-center justify-center mx-auto mb-3 shadow-sm`}
              >
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
            <span className="text-xs font-bold uppercase tracking-widest text-purple-500 mb-3 block">
              How It Works
            </span>
            <h2 className="text-3xl font-extrabold text-gray-900">
              Earn PayFlex Points in 4 steps
            </h2>
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

      {/* How You Benefit + Redeem */}
      <section className="max-w-4xl mx-auto px-6 py-20">
        <div className="grid md:grid-cols-2 gap-12 items-start">
          <div>
            <span className="text-xs font-bold uppercase tracking-widest text-purple-500 mb-3 block">
              How You Benefit
            </span>
            <h2 className="text-3xl font-extrabold text-gray-900 mb-4">
              More value, every time you transact
            </h2>
            <p className="text-sm text-gray-600 leading-relaxed mb-6">
              Whether paying bills, shopping online, or booking services, the PayFlex Loyalty
              Program rewards you every time you transact.
            </p>
            <div className="space-y-3">
              {[
                [Star, 'Earn PayFlex Points on every eligible transaction.'],
                [Gift, 'Redeem PayFlex Points on future payments to save more.'],
                [TrendingUp, 'Access exclusive offers and discounts when using PayFlex Points.'],
                [Zap, 'Maximize rewards on every payment you make with PayFlex.'],
              ].map(([Icon, text]) => (
                <div key={text} className="flex items-start gap-3 text-sm text-gray-700">
                  <div className="w-7 h-7 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0">
                    <Icon size={14} className="text-purple-500" />
                  </div>
                  {text}
                </div>
              ))}
            </div>

            <div className="mt-8">
              <span className="text-xs font-bold uppercase tracking-widest text-purple-500 mb-3 block">
                How to Redeem
              </span>
              <div className="space-y-3">
                {[
                  [RefreshCw, 'Check your PayFlex Points balance by logging into your account.'],
                  [
                    Gift,
                    'Use your points on your next eligible transaction to reduce the payable amount.',
                  ],
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
          </div>

          <div className="space-y-6">
            <div>
              <span className="text-xs font-bold uppercase tracking-widest text-purple-500 mb-3 block">
                Special Occasion Rewards
              </span>
              <div className="space-y-3">
                {[
                  [
                    Star,
                    'Birthday & Anniversaries',
                    "Celebrate life's moments with PayFlex. Earn PayFlex Points while making payments for birthdays, anniversaries, and other special occasions.",
                  ],
                  [
                    Gift,
                    'Festivals & Holidays',
                    'Collect points during Diwali, New Year, and every festival — then redeem them on your next transaction while enjoying exclusive offers.',
                  ],
                ].map(([Icon, title, body]) => (
                  <div
                    key={title}
                    className="flex gap-4 bg-white rounded-xl border border-gray-100 shadow-sm p-4"
                  >
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

            <div>
              <span className="text-xs font-bold uppercase tracking-widest text-purple-500 mb-3 block">
                Corporate &amp; Bulk Transactions
              </span>
              <div className="flex gap-4 bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <div className="w-9 h-9 rounded-xl bg-purple-50 flex items-center justify-center flex-shrink-0">
                  <Briefcase size={16} className="text-purple-500" />
                </div>
                <div>
                  <p className="font-semibold text-gray-800 text-sm">
                    Bulk Payments &amp; Corporate Events
                  </p>
                  <p className="text-xs text-gray-500 mt-0.5 leading-relaxed">
                    Planning bulk payments for corporate events, office functions, or client
                    gifting? Use PayFlex for large transactions to earn PayFlex Points, which can be
                    redeemed later to enjoy extra value and discounts.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Eligible payment modes + Expiry */}
      <section className="bg-gray-50 py-14">
        <div className="max-w-3xl mx-auto px-6">
          <div className="text-center mb-8">
            <h3 className="text-lg font-bold text-gray-800 mb-4">Eligible Payment Modes</h3>
            <div className="flex flex-wrap justify-center gap-3">
              {['Debit Card', 'Credit Card', 'UPI', 'Net Banking', 'Supported Wallets'].map(
                (mode) => (
                  <span
                    key={mode}
                    className="bg-white border border-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-xl shadow-sm"
                  >
                    {mode}
                  </span>
                )
              )}
            </div>
            <p className="text-xs text-gray-400 mt-3">
              PayFlex Points cannot be earned on transactions paid using PayFlex Points themselves.
            </p>
          </div>

          <div className="grid sm:grid-cols-2 gap-4 mt-6">
            <div className="bg-white rounded-2xl border border-amber-200 p-5 shadow-sm">
              <div className="flex items-center gap-2 mb-2">
                <Clock size={16} className="text-amber-500" />
                <h4 className="font-bold text-gray-800 text-sm">Expiry of PayFlex Points</h4>
              </div>
              <ul className="space-y-2">
                {[
                  'PayFlex Points must be used before their validity date.',
                  'Expired points cannot be revalidated.',
                ].map((t, i) => (
                  <li key={i} className="flex items-start gap-2 text-xs text-gray-500">
                    <span className="w-4 h-4 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">
                      {i + 1}
                    </span>
                    {t}
                  </li>
                ))}
              </ul>
            </div>
            <div className="bg-white rounded-2xl border border-purple-200 p-5 shadow-sm">
              <div className="flex items-center gap-2 mb-2">
                <Star size={16} className="text-purple-500" fill="currentColor" />
                <h4 className="font-bold text-gray-800 text-sm">Points Value</h4>
              </div>
              <div className="text-center py-2">
                <p className="text-3xl font-black text-purple-600">1 pt = ₹1</p>
                <p className="text-xs text-gray-400 mt-1">
                  Valid for <strong>30 days</strong> from credit date
                </p>
                <p className="text-xs text-gray-400 mt-1">
                  Earn up to <strong>2%</strong> on every eligible transaction
                </p>
              </div>
            </div>
          </div>
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
              <span className="w-5 h-5 rounded-full bg-purple-50 text-purple-500 flex items-center justify-center flex-shrink-0 text-xs font-bold mt-0.5">
                {i + 1}
              </span>
              {rule}
            </div>
          ))}
        </div>
      </section>

      {/* CTA */}
      <section className="bg-gradient-to-br from-purple-600 to-primary-600 py-16 text-center text-white">
        <h2 className="text-2xl font-extrabold mb-3">Start Earning Today</h2>
        <p className="text-white/80 text-sm mb-2">
          Every transaction is a step toward your next reward.
        </p>
        <p className="text-white/60 text-xs mb-8">
          Enjoy payments with extra value for daily essentials, lifestyle, travel, entertainment,
          and more.
        </p>
        <div className="flex flex-col sm:flex-row gap-3 justify-center">
          <Link
            to="/register"
            className="bg-white text-purple-700 font-bold px-8 py-3 rounded-xl hover:bg-gray-50 transition-colors inline-block"
          >
            Create Free Account
          </Link>
          <Link
            to="/my-points"
            className="border border-white/40 text-white font-semibold px-8 py-3 rounded-xl hover:bg-white/10 transition-colors inline-block"
          >
            View My Points
          </Link>
        </div>
      </section>
    </div>
  )
}
