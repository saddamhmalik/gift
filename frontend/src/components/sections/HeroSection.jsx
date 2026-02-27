import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { Search, Sparkles, Shield, Zap } from 'lucide-react'

const BADGES = [
  { Icon: Zap,      text: 'Instant Delivery'   },
  { Icon: Shield,   text: 'Secure & Trusted'   },
  { Icon: Sparkles, text: 'Earn PayFlex Points' },
]

export default function HeroSection() {
  const [query, setQuery] = useState('')
  const navigate = useNavigate()

  const handleSearch = (e) => {
    e.preventDefault()
    if (query.trim()) navigate(`/search?q=${encodeURIComponent(query.trim())}`)
  }

  return (
    <section className="relative overflow-hidden bg-gradient-to-br from-primary-600 via-primary-500 to-amber-400 text-white">
      {/* Background decorations */}
      <div className="absolute inset-0 overflow-hidden pointer-events-none">
        <div className="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-white/10 blur-3xl" />
        <div className="absolute -bottom-32 -left-32 w-96 h-96 rounded-full bg-white/10 blur-3xl" />
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[40rem] h-[40rem] rounded-full bg-white/5 blur-3xl" />
        {/* Floating emoji decorations */}
        <div className="absolute top-8  left-[8%]  text-5xl opacity-20 animate-bounce" style={{animationDelay:'0.2s',animationDuration:'3s'}}>🎁</div>
        <div className="absolute top-16 right-[10%] text-4xl opacity-15 animate-bounce" style={{animationDelay:'1s',  animationDuration:'4s'}}>🎉</div>
        <div className="absolute bottom-12 left-[15%] text-3xl opacity-15 animate-bounce" style={{animationDelay:'0.5s',animationDuration:'3.5s'}}>✨</div>
        <div className="absolute bottom-8  right-[8%] text-4xl opacity-20 animate-bounce" style={{animationDelay:'1.5s',animationDuration:'2.8s'}}>💝</div>
      </div>

      <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28 text-center">
        {/* Pre-title */}
        <div className="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-1.5 rounded-full text-sm font-medium mb-6 border border-white/30">
          <Sparkles size={14} />
          India's Smart Payments &amp; Rewards Platform
        </div>

        {/* Headline */}
        <h1 className="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-tight mb-4 tracking-tight">
          Pay Smart. Earn More.<br className="hidden sm:block" />
          <span className="relative inline-block">
            Every Time.
            <svg className="absolute -bottom-2 left-0 w-full" viewBox="0 0 300 12" fill="none">
              <path d="M2 8 Q75 2 150 8 Q225 14 298 8" stroke="rgba(255,255,255,0.6)" strokeWidth="3" strokeLinecap="round" fill="none"/>
            </svg>
          </span>
        </h1>
        <p className="text-lg sm:text-xl text-white/80 max-w-xl mx-auto mb-10">
          Browse 500+ gift cards from top brands and earn PayFlex Points on every purchase. 1 Point = ₹1. Instant delivery, zero hassle.
        </p>

        {/* Search */}
        <form onSubmit={handleSearch} className="max-w-lg mx-auto flex gap-2 mb-12">
          <div className="flex-1 flex items-center gap-2 bg-white rounded-2xl px-4 py-3 shadow-xl">
            <Search size={18} className="text-gray-400 flex-shrink-0" />
            <input
              type="text"
              placeholder="Search Amazon, Flipkart, Swiggy…"
              value={query}
              onChange={e => setQuery(e.target.value)}
              className="flex-1 outline-none text-gray-800 placeholder-gray-400 bg-transparent text-sm"
            />
          </div>
          <button type="submit" className="px-6 py-3 bg-gray-900 hover:bg-gray-800 text-white rounded-2xl font-semibold text-sm shadow-xl transition-colors flex-shrink-0">
            Search
          </button>
        </form>

        {/* Badges */}
        <div className="flex flex-wrap items-center justify-center gap-4">
          {BADGES.map(({ Icon, text }) => (
            <div key={text} className="flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-xl border border-white/20 text-sm">
              <Icon size={15} className="text-white" />
              <span className="font-medium">{text}</span>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
