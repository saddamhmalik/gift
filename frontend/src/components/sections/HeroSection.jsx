import { useState, useRef, useEffect, useDeferredValue, useId } from 'react'
import { useNavigate, Link } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { Search, Zap, Shield, Star, ArrowRight, Sparkles } from 'lucide-react'
import { searchProducts } from '../../api/products'

const STATS = [
  { value: '500+', label: 'Brands' },
  { value: 'Up to 2%', label: 'Rewards back' },
  { value: '₹1', label: 'Per point' },
  { value: '24/7', label: 'Instant delivery' },
]

const BADGES = [
  { Icon: Zap, text: 'Instant Delivery', color: 'text-amber-400' },
  { Icon: Shield, text: 'Secure Payments', color: 'text-emerald-400' },
  { Icon: Star, text: 'Earn PayFlex Points', color: 'text-primary-400' },
]

const POPULAR = ['Amazon', 'Flipkart', 'BookMyShow', 'Swiggy', 'Uber', 'Zomato', 'Netflix']

export default function HeroSection() {
  const [query, setQuery] = useState('')
  const [open, setOpen] = useState(false)
  const [activeIndex, setActiveIndex] = useState(-1)
  const navigate = useNavigate()
  const wrapRef = useRef(null)
  const listId = useId()
  const deferredQuery = useDeferredValue(query.trim())

  const { data, isFetching } = useQuery({
    queryKey: ['search-suggest', deferredQuery],
    queryFn: () => searchProducts({ q: deferredQuery, per_page: 6 }),
    enabled: deferredQuery.length >= 2,
    staleTime: 30_000,
  })

  const products = data?.data?.data ?? []
  const brandMatches =
    deferredQuery.length >= 2
      ? POPULAR.filter((b) => b.toLowerCase().startsWith(deferredQuery.toLowerCase())).slice(0, 3)
      : []

  const suggestions = [
    ...brandMatches.map((name) => ({ type: 'brand', key: `brand-${name}`, name })),
    ...products.map((p) => ({
      type: 'product',
      key: `product-${p.slug}`,
      name: p.name,
      slug: p.slug,
      image: p.thumbnail_url || p.image_url,
    })),
  ]

  const showDropdown = open && deferredQuery.length >= 2 && (suggestions.length > 0 || isFetching)

  useEffect(() => {
    setActiveIndex(-1)
  }, [deferredQuery])

  useEffect(() => {
    const onPointerDown = (e) => {
      if (wrapRef.current && !wrapRef.current.contains(e.target)) {
        setOpen(false)
      }
    }
    document.addEventListener('mousedown', onPointerDown)
    return () => document.removeEventListener('mousedown', onPointerDown)
  }, [])

  const goToSuggestion = (item) => {
    setOpen(false)
    if (item.type === 'product') {
      navigate(`/products/${item.slug}`)
      return
    }
    navigate(`/search?q=${encodeURIComponent(item.name)}`)
  }

  const handleSearch = (e) => {
    e.preventDefault()
    const trimmed = query.trim()
    if (!trimmed) return
    if (activeIndex >= 0 && suggestions[activeIndex]) {
      goToSuggestion(suggestions[activeIndex])
      return
    }
    setOpen(false)
    navigate(`/search?q=${encodeURIComponent(trimmed)}`)
  }

  const handleKeyDown = (e) => {
    if (!showDropdown || suggestions.length === 0) return
    if (e.key === 'ArrowDown') {
      e.preventDefault()
      setActiveIndex((i) => (i + 1) % suggestions.length)
    } else if (e.key === 'ArrowUp') {
      e.preventDefault()
      setActiveIndex((i) => (i <= 0 ? suggestions.length - 1 : i - 1))
    } else if (e.key === 'Escape') {
      setOpen(false)
      setActiveIndex(-1)
    }
  }

  return (
    <section className="relative overflow-hidden bg-surface-950 grain min-h-[calc(100vh-4rem)] flex items-center">
      {/* ── Mesh gradient blobs ── */}
      <div className="absolute inset-0 pointer-events-none overflow-hidden">
        {/* Main purple blob */}
        <div className="absolute -top-40 -left-40 w-[600px] h-[600px] rounded-full bg-primary-600/30 blur-[120px] animate-float-slow" />
        {/* Brand orange blob */}
        <div
          className="absolute -bottom-40 -right-20 w-[500px] h-[500px] rounded-full bg-brand/20 blur-[120px] animate-float"
          style={{ animationDelay: '3s' }}
        />
        {/* Indigo accent */}
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[300px] rounded-full bg-indigo-900/40 blur-[100px]" />
        {/* Grid overlay */}
        <div
          className="absolute inset-0 opacity-[0.03]"
          style={{
            backgroundImage:
              'linear-gradient(rgba(255,255,255,1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,1) 1px, transparent 1px)',
            backgroundSize: '60px 60px',
          }}
        />
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
          <span
            className="bg-clip-text text-transparent bg-gradient-to-r from-primary-400 via-violet-300 to-brand animate-gradient bg-animate"
            style={{ backgroundSize: '200%' }}
          >
            Earn More.
          </span>
        </h1>

        <p className="text-lg sm:text-xl text-slate-400 max-w-2xl mx-auto mb-10 leading-relaxed">
          Browse 500+ gift cards from India's top brands. Earn up to{' '}
          <span className="text-white font-semibold">2% PayFlex Points</span> on every purchase —
          redeemable instantly.{' '}
          <span className="text-primary-400 font-semibold">1 Point = ₹1.</span>
        </p>

        {/* Search */}
        <form onSubmit={handleSearch} className="max-w-xl mx-auto mb-5" ref={wrapRef} autoComplete="off">
          <div className="relative">
            <div className="flex items-center gap-2 bg-white/8 border border-white/12 hover:border-primary-500/50 focus-within:border-primary-500/70 focus-within:bg-white/12 rounded-2xl px-4 py-3 transition-all duration-300 shadow-xl shadow-black/30">
              <Search size={18} className="text-slate-500 flex-shrink-0" />
              <input
                type="text"
                role="combobox"
                aria-expanded={showDropdown}
                aria-controls={listId}
                aria-autocomplete="list"
                aria-activedescendant={
                  activeIndex >= 0 && suggestions[activeIndex]
                    ? `${listId}-${activeIndex}`
                    : undefined
                }
                placeholder="Search Amazon, Flipkart, Swiggy…"
                value={query}
                onChange={(e) => {
                  setQuery(e.target.value)
                  setOpen(true)
                }}
                onFocus={() => setOpen(true)}
                onKeyDown={handleKeyDown}
                className="flex-1 outline-none bg-transparent text-white placeholder-slate-500 text-sm"
              />
              <button
                type="submit"
                className="px-5 py-2 bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 text-white rounded-xl font-semibold text-sm transition-all shadow-md shadow-primary-900/40 flex-shrink-0"
              >
                Search
              </button>
            </div>

            {showDropdown && (
              <div
                id={listId}
                role="listbox"
                className="absolute left-0 right-0 top-full mt-2 z-30 overflow-hidden rounded-2xl border border-white/12 bg-surface-900/95 backdrop-blur-xl shadow-2xl shadow-black/50 text-left"
              >
                {isFetching && suggestions.length === 0 ? (
                  <p className="px-4 py-3 text-sm text-slate-400">Searching…</p>
                ) : (
                  <ul className="py-1 max-h-80 overflow-y-auto">
                    {suggestions.map((item, index) => (
                      <li key={item.key} role="option" aria-selected={index === activeIndex} id={`${listId}-${index}`}>
                        <button
                          type="button"
                          onMouseDown={(e) => e.preventDefault()}
                          onClick={() => goToSuggestion(item)}
                          onMouseEnter={() => setActiveIndex(index)}
                          className={`w-full flex items-center gap-3 px-4 py-2.5 text-left transition-colors ${
                            index === activeIndex ? 'bg-white/10' : 'hover:bg-white/8'
                          }`}
                        >
                          {item.type === 'product' ? (
                            item.image ? (
                              <img
                                src={item.image}
                                alt=""
                                className="w-9 h-9 rounded-lg object-cover bg-white/10 flex-shrink-0"
                              />
                            ) : (
                              <div className="w-9 h-9 rounded-lg bg-white/10 flex-shrink-0" />
                            )
                          ) : (
                            <div className="w-9 h-9 rounded-lg bg-primary-500/20 text-primary-300 flex items-center justify-center flex-shrink-0">
                              <Search size={14} />
                            </div>
                          )}
                          <div className="min-w-0 flex-1">
                            <p className="text-sm text-white font-medium truncate">{item.name}</p>
                            <p className="text-[11px] text-slate-500">
                              {item.type === 'brand' ? 'Brand search' : 'Gift card'}
                            </p>
                          </div>
                        </button>
                      </li>
                    ))}
                  </ul>
                )}
                {deferredQuery.length >= 2 && (
                  <button
                    type="button"
                    onMouseDown={(e) => e.preventDefault()}
                    onClick={() => {
                      setOpen(false)
                      navigate(`/search?q=${encodeURIComponent(deferredQuery)}`)
                    }}
                    className="w-full border-t border-white/10 px-4 py-2.5 text-sm text-primary-300 hover:bg-white/5 text-left font-medium"
                  >
                    See all results for “{deferredQuery}”
                  </button>
                )}
              </div>
            )}
          </div>
        </form>

        {/* Popular search chips */}
        <div className="flex flex-wrap items-center justify-center gap-2 mb-14 text-xs">
          <span className="text-slate-500 mr-1">Popular:</span>
          {POPULAR.map((b) => (
            <Link
              key={b}
              to={`/search?q=${encodeURIComponent(b)}`}
              className="px-3 py-1.5 rounded-full bg-white/6 border border-white/10 text-slate-400 hover:text-white hover:border-primary-500/40 hover:bg-white/10 transition-all"
            >
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
            <div
              key={text}
              className="flex items-center gap-2 bg-white/5 border border-white/8 backdrop-blur-sm px-4 py-2 rounded-xl text-sm"
            >
              <Icon size={14} className={color} />
              <span className="text-slate-300 font-medium">{text}</span>
            </div>
          ))}
          <Link
            to="/loyalty"
            className="flex items-center gap-2 bg-brand/10 border border-brand/25 px-4 py-2 rounded-xl text-sm text-brand hover:bg-brand/20 transition-colors"
          >
            <Star size={14} fill="currentColor" />
            <span className="font-semibold">Loyalty Program</span>
            <ArrowRight size={12} />
          </Link>
        </div>
      </div>
    </section>
  )
}
