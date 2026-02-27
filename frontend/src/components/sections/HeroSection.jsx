import { useState } from 'react'
import { useNavigate, Link } from 'react-router-dom'
import { Search, Zap, Shield, Star, ArrowRight, Sparkles } from 'lucide-react'

const STATS = [
  { value: '500+',  label: 'Brands'          },
  { value: 'Up to 2%', label: 'Rewards back' },
  { value: '₹1',   label: 'Per point'        },
  { value: '24/7', label: 'Instant delivery' },
]

const BADGES = [
  { Icon: Zap,      text: 'Instant Delivery',    color: 'text-amber-400' },
  { Icon: Shield,   text: 'Secure Payments',      color: 'text-emerald-400' },
  { Icon: Star,     text: 'Earn PayFlex Points',  color: 'text-primary-400' },
]

export default function HeroSection() {
  const [query, setQuery] = useState('')
  const navigate = useNavigate()

  const handleSearch = (e) => {
    e.preventDefault()
    if (query.trim()) navigate(`/search?q=${encodeURIComponent(query.trim())}`)
  }

  return (
    <section className="relative overflow-hidden bg-surface-950 grain min-h-[calc(100vh-4rem)] flex items-center">

      {/* ── Mesh gradient blobs ── */}
      <div className="absolute inset-0 pointer-events-none overflow-hidden">
        {/* Main purple blob */}
        <div className="absolute -top-40 -left-40 w-[600px] h-[600px] rounded-full bg-primary-600/30 blur-[120px] animate-float-slow" />
        {/* Brand orange blob */}
        <div className="absolute -bottom-40 -right-20 w-[500px] h-[500px] rounded-full bg-brand/20 blur-[120px] animate-float" style={{animationDelay:'3s'}} />
        {/* Indigo accent */}
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[300px] rounded-full bg-indigo-900/40 blur-[100px]" />
        {/* Grid overlay */}
        <div className="absolute inset-0 opacity-[0.03]" style={{
          backgroundImage: 'linear-gradient(rgba(255,255,255,1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,1) 1px, transparent 1px)',
          backgroundSize: '60px 60px'
        }} />
      </div>

      {/* ── Floating card decorations ── */}
      <div className="absolute top-32 right-[8%] hidden lg:block animate-float opacity-80" style={{animationDelay:'1s'}}>
        <div className="bg-white/8 backdrop-blur-md border border-white/12 rounded-2xl px-4 py-3 flex items-center gap-3 shadow-xl">
          <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-lg">🎁</div>
          <div>
            <p className="text-white text-xs font-semibold">Amazon Pay</p>
            <p className="text-emerald-400 text-xs font-bold">+₹50 points</p>
          </div>
        </div>
      </div>

      <div className="absolute bottom-32 right-[12%] hidden lg:block animate-float opacity-70" style={{animationDelay:'2.5s'}}>
        <div className="bg-white/8 backdrop-blur-md border border-white/12 rounded-2xl px-4 py-3 flex items-center gap-3 shadow-xl">
          <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-violet-400 to-purple-500 flex items-center justify-center text-lg">⭐</div>
          <div>
            <p className="text-white text-xs font-semibold">PayFlex Points</p>
            <p className="text-primary-400 text-xs font-bold">1 Point = ₹1</p>
          </div>
        </div>
      </div>

      <div className="absolute top-40 left-[6%] hidden xl:block animate-float opacity-60" style={{animationDelay:'4s'}}>
        <div className="bg-white/8 backdrop-blur-md border border-white/12 rounded-2xl px-4 py-3 shadow-xl">
          <p className="text-slate-400 text-[10px] mb-0.5">Total saved this month</p>
          <p className="text-white text-base font-extrabold">₹2,450</p>
        </div>
      </div>

      {/* ── Main content ── */}
      <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center w-full">

        {/* Eyebrow */}
        <div className="inline-flex items-center gap-2 bg-primary-500/15 border border-primary-500/30 text-primary-300 text-xs font-semibold px-4 py-2 rounded-full mb-8 backdrop-blur-sm">
          <Sparkles size={12} className="text-primary-400" />
          India's Smart Payments &amp; Rewards Platform
          <Sparkles size={12} className="text-primary-400" />
        </div>

        {/* Headline */}
        <h1 className="text-5xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight leading-[1.05] mb-6">
          <span className="text-white">Pay Smart.</span>
          <br />
          <span className="bg-clip-text text-transparent bg-gradient-to-r from-primary-400 via-violet-300 to-brand animate-gradient bg-animate" style={{backgroundSize:'200%'}}>
            Earn More.
          </span>
        </h1>

        <p className="text-lg sm:text-xl text-slate-400 max-w-2xl mx-auto mb-10 leading-relaxed">
          Browse 500+ gift cards from India's top brands. Earn up to <span className="text-white font-semibold">2% PayFlex Points</span> on every purchase — redeemable instantly. <span className="text-primary-400 font-semibold">1 Point = ₹1.</span>
        </p>

        {/* Search */}
        <form onSubmit={handleSearch} className="max-w-xl mx-auto mb-5">
          <div className="flex items-center gap-2 bg-white/8 border border-white/12 hover:border-primary-500/50 focus-within:border-primary-500/70 focus-within:bg-white/12 rounded-2xl px-4 py-3 transition-all duration-300 shadow-xl shadow-black/30">
            <Search size={18} className="text-slate-500 flex-shrink-0" />
            <input
              type="text"
              placeholder="Search Amazon, Flipkart, Swiggy…"
              value={query}
              onChange={e => setQuery(e.target.value)}
              className="flex-1 outline-none bg-transparent text-white placeholder-slate-500 text-sm"
            />
            <button
              type="submit"
              className="px-5 py-2 bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 text-white rounded-xl font-semibold text-sm transition-all shadow-md shadow-primary-900/40 flex-shrink-0"
            >
              Search
            </button>
          </div>
        </form>

        {/* Quick links */}
        <div className="flex flex-wrap items-center justify-center gap-2 mb-14 text-xs">
          {['Amazon Pay', 'Flipkart', 'BookMyShow', 'Uber', 'Swiggy'].map(b => (
            <Link key={b} to={`/categories`} className="px-3 py-1.5 rounded-full bg-white/6 border border-white/10 text-slate-400 hover:text-white hover:border-white/20 transition-all">
              {b}
            </Link>
          ))}
        </div>

        {/* Stats row */}
        <div className="flex flex-wrap items-center justify-center gap-8 mb-12">
          {STATS.map(({ value, label }) => (
            <div key={label} className="text-center">
              <p className="text-2xl font-extrabold text-white">{value}</p>
              <p className="text-xs text-slate-500 mt-0.5">{label}</p>
            </div>
          ))}
        </div>

        {/* Trust badges */}
        <div className="flex flex-wrap items-center justify-center gap-3">
          {BADGES.map(({ Icon, text, color }) => (
            <div key={text} className="flex items-center gap-2 bg-white/5 border border-white/8 backdrop-blur-sm px-4 py-2 rounded-xl text-sm">
              <Icon size={14} className={color} />
              <span className="text-slate-300 font-medium">{text}</span>
            </div>
          ))}
          <Link to="/loyalty" className="flex items-center gap-2 bg-brand/10 border border-brand/25 px-4 py-2 rounded-xl text-sm text-brand hover:bg-brand/20 transition-colors">
            <Star size={14} fill="currentColor" />
            <span className="font-semibold">Loyalty Program</span>
            <ArrowRight size={12} />
          </Link>
        </div>
      </div>
    </section>
  )
}
