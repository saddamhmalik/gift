import { useState, useEffect, useRef } from 'react'
import { Link, useNavigate, useLocation } from 'react-router-dom'
import {
  Zap,
  Search,
  User,
  LogOut,
  ChevronDown,
  Menu,
  X,
  ShoppingBag,
  Tag,
  Star,
  CreditCard,
} from 'lucide-react'
import { useQuery } from '@tanstack/react-query'
import { useAuth } from '../../contexts/AuthContext'
import { getTags } from '../../api/tags'
import { getLoyaltyBalance } from '../../api/loyalty'

const NAV_LINKS = [
  { to: '/', label: 'Home' },
  { to: '/categories', label: 'Categories' },
  { to: '/hot-deals', label: '🔥 Deals' },
  { to: '/trending', label: 'Trending' },
  { to: '/new-arrivals', label: 'New' },
]

export default function Navbar() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()
  const [scrolled, setScrolled] = useState(false)
  const [menuOpen, setMenuOpen] = useState(false)
  const [userMenuOpen, setUserMenuOpen] = useState(false)
  const [tagsOpen, setTagsOpen] = useState(false)
  const [searchVal, setSearchVal] = useState('')
  const [searchOpen, setSearchOpen] = useState(false)
  const tagsRef = useRef(null)

  const { data: tagsData } = useQuery({ queryKey: ['tags'], queryFn: getTags, staleTime: 300_000 })
  const tags = tagsData?.data ?? []

  const { data: loyaltyData } = useQuery({
    queryKey: ['loyalty', 'balance'],
    queryFn: getLoyaltyBalance,
    enabled: !!user,
    staleTime: 1000 * 60,
  })
  const pointBalance = loyaltyData?.data?.balance ?? 0

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 8)
    window.addEventListener('scroll', onScroll, { passive: true })
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  useEffect(() => {
    setMenuOpen(false)
    setSearchOpen(false)
    setTagsOpen(false)
  }, [location.pathname])

  const handleSearch = (e) => {
    e.preventDefault()
    if (searchVal.trim()) {
      navigate(`/search?q=${encodeURIComponent(searchVal.trim())}`)
      setSearchOpen(false)
    }
  }

  const handleLogout = async () => {
    await logout()
    setUserMenuOpen(false)
    navigate('/')
  }

  const isActive = (to) => location.pathname === to || location.pathname.startsWith(to + '/')

  return (
    <header
      className={`fixed inset-x-0 top-0 z-50 transition-all duration-300 ${
        scrolled
          ? 'bg-surface-950/95 backdrop-blur-xl shadow-lg shadow-black/20 border-b border-white/5'
          : 'bg-surface-950/80 backdrop-blur-md'
      }`}
    >
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">
          {/* ── Logo ── */}
          <Link to="/" className="flex items-center gap-2.5 flex-shrink-0 group">
            <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-brand flex items-center justify-center shadow-glow-primary transition-transform group-hover:scale-105">
              <Zap size={16} className="text-white" />
            </div>
            <span className="text-lg font-extrabold text-white tracking-tight">
              Pay<span className="text-primary-400">Flex</span>
            </span>
          </Link>

          {/* ── Desktop nav ── */}
          <nav className="hidden md:flex items-center gap-1 text-sm">
            {NAV_LINKS.map(({ to, label }) => (
              <Link
                key={to}
                to={to}
                className={`px-3 py-2 rounded-lg font-medium transition-all duration-150 ${
                  isActive(to)
                    ? 'text-white bg-white/10'
                    : 'text-slate-400 hover:text-white hover:bg-white/8'
                }`}
              >
                {label}
              </Link>
            ))}

            {/* Tags dropdown */}
            <div className="relative" ref={tagsRef}>
              <button
                onClick={() => setTagsOpen((v) => !v)}
                className={`flex items-center gap-1.5 px-3 py-2 rounded-lg font-medium transition-all duration-150 ${
                  tagsOpen
                    ? 'text-white bg-white/10'
                    : 'text-slate-400 hover:text-white hover:bg-white/8'
                }`}
              >
                <Tag size={13} />
                Shop by Tags
                <ChevronDown
                  size={12}
                  className={`transition-transform ${tagsOpen ? 'rotate-180' : ''}`}
                />
              </button>

              {tagsOpen && (
                <>
                  <div className="fixed inset-0 z-40" onClick={() => setTagsOpen(false)} />
                  <div className="absolute top-full left-0 mt-2 w-72 bg-slate-950 border border-white/10 rounded-2xl shadow-2xl shadow-black/50 z-50 overflow-hidden">
                    <div className="px-4 pt-3 pb-2 border-b border-white/8">
                      <p className="text-[11px] font-bold uppercase tracking-widest text-slate-500">
                        Shop by Tags
                      </p>
                    </div>
                    <div className="p-3 flex flex-wrap gap-2 max-h-72 overflow-y-auto">
                      {tags.length === 0 && (
                        <p className="text-xs text-slate-500 px-1 py-2">No tags available.</p>
                      )}
                      {tags.map((tag) => (
                        <Link
                          key={tag.id}
                          to={`/tags/${tag.slug}`}
                          className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-primary-500/15 border border-primary-400/30 text-primary-300 hover:bg-primary-500/25 hover:text-white transition-all duration-150"
                        >
                          {tag.name}
                        </Link>
                      ))}
                    </div>
                    <div className="px-4 py-2.5 border-t border-white/8">
                      <Link
                        to="/tags"
                        className="text-xs font-bold text-primary-400 hover:text-primary-300 transition-colors flex items-center gap-1"
                      >
                        View all tags →
                      </Link>
                    </div>
                  </div>
                </>
              )}
            </div>
          </nav>

          {/* ── Right cluster ── */}
          <div className="flex items-center gap-2">
            {/* Search — desktop */}
            {searchOpen ? (
              <form
                onSubmit={handleSearch}
                className="hidden sm:flex items-center gap-2 bg-white/10 border border-white/15 rounded-xl px-3 py-2 w-56 focus-within:border-primary-400 transition-all"
              >
                <Search size={14} className="text-slate-400 flex-shrink-0" />
                <input
                  autoFocus
                  type="text"
                  placeholder="Search PayFlex…"
                  value={searchVal}
                  onChange={(e) => setSearchVal(e.target.value)}
                  className="bg-transparent outline-none flex-1 text-white placeholder-slate-500 text-sm"
                />
                <button
                  type="button"
                  onClick={() => setSearchOpen(false)}
                  className="text-slate-500 hover:text-slate-300"
                >
                  <X size={14} />
                </button>
              </form>
            ) : (
              <button
                onClick={() => setSearchOpen(true)}
                className="hidden sm:flex w-9 h-9 rounded-xl bg-white/8 hover:bg-white/15 items-center justify-center text-slate-400 hover:text-white transition-all"
                aria-label="Search"
              >
                <Search size={16} />
              </button>
            )}

            {/* Auth */}
            {user ? (
              <div className="flex items-center gap-2">
                {/* Points pill */}
                {pointBalance > 0 && (
                  <Link
                    to="/my-points"
                    className="hidden sm:flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-amber-500/15 border border-amber-400/25 text-amber-300 hover:bg-amber-500/25 transition-all text-xs font-bold"
                    title="My PayFlex Points"
                  >
                    <Star size={11} fill="currentColor" />
                    {pointBalance.toFixed(0)} pts
                  </Link>
                )}

                <div className="relative">
                  <button
                    onClick={() => setUserMenuOpen((v) => !v)}
                    className="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white/8 hover:bg-white/15 transition-all"
                  >
                    <div className="w-6 h-6 rounded-full bg-gradient-to-br from-primary-400 to-brand flex items-center justify-center text-white text-[10px] font-bold">
                      {(user.first_name?.[0] || user.name?.[0] || 'U').toUpperCase()}
                    </div>
                    <span className="hidden lg:inline text-sm font-medium text-slate-300">
                      {user.first_name || user.name?.split(' ')[0]}
                    </span>
                    <ChevronDown
                      size={13}
                      className={`text-slate-400 transition-transform ${userMenuOpen ? 'rotate-180' : ''}`}
                    />
                  </button>

                  {userMenuOpen && (
                    <>
                      <div className="fixed inset-0 z-40" onClick={() => setUserMenuOpen(false)} />
                      <div className="absolute right-0 mt-2 w-52 bg-surface-900 border border-white/10 rounded-2xl shadow-xl shadow-black/40 py-1.5 z-50 overflow-hidden">
                        <div className="px-4 py-2.5 border-b border-white/8 mb-1">
                          <p className="text-xs text-slate-500">Signed in as</p>
                          <p className="text-sm font-semibold text-white truncate">{user.email}</p>
                          {pointBalance > 0 && (
                            <div className="flex items-center gap-1 mt-1">
                              <Star size={11} className="text-amber-400" fill="currentColor" />
                              <span className="text-xs font-bold text-amber-400">
                                {pointBalance.toFixed(0)} PayFlex Points
                              </span>
                            </div>
                          )}
                        </div>
                        <Link
                          to="/profile"
                          onClick={() => setUserMenuOpen(false)}
                          className="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-white/8 transition-colors"
                        >
                          <User size={14} /> My Account
                        </Link>
                        <Link
                          to="/profile"
                          onClick={() => setUserMenuOpen(false)}
                          className="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-white/8 transition-colors"
                        >
                          <User size={14} /> My Profile
                        </Link>
                        <Link
                          to="/orders"
                          onClick={() => setUserMenuOpen(false)}
                          className="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-white/8 transition-colors"
                        >
                          <ShoppingBag size={14} /> My Orders
                        </Link>
                        <Link
                          to="/my-points"
                          onClick={() => setUserMenuOpen(false)}
                          className="flex items-center gap-2.5 px-4 py-2 text-sm text-amber-300 hover:text-amber-200 hover:bg-amber-500/10 transition-colors"
                        >
                          <Star size={14} /> My Points{' '}
                          {pointBalance > 0 && (
                            <span className="ml-auto text-xs bg-amber-500/20 px-1.5 py-0.5 rounded-full">
                              {pointBalance.toFixed(0)}
                            </span>
                          )}
                        </Link>
                        <Link
                          to="/check-balance"
                          onClick={() => setUserMenuOpen(false)}
                          className="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-white/8 transition-colors"
                        >
                          <CreditCard size={14} /> Check Balance
                        </Link>
                        <div className="my-1 border-t border-white/8" />
                        <button
                          onClick={handleLogout}
                          className="flex items-center gap-2.5 w-full px-4 py-2 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-colors"
                        >
                          <LogOut size={14} /> Sign Out
                        </button>
                      </div>
                    </>
                  )}
                </div>
              </div>
            ) : (
              <div className="hidden sm:flex items-center gap-2">
                <Link
                  to="/check-balance"
                  className="px-4 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-white/10 transition-all"
                >
                  Check balance
                </Link>
                <Link
                  to="/login"
                  className="px-4 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-white/10 transition-all"
                >
                  Login
                </Link>
                <Link
                  to="/register"
                  className="px-4 py-2 rounded-xl text-sm font-bold bg-gradient-to-r from-primary-600 to-primary-500 text-white hover:from-primary-500 hover:to-primary-400 transition-all shadow-md shadow-primary-900/30"
                >
                  Sign Up
                </Link>
              </div>
            )}

            {/* Mobile toggle */}
            <button
              className="md:hidden w-9 h-9 rounded-xl bg-white/8 hover:bg-white/15 flex items-center justify-center text-slate-400 hover:text-white transition-all"
              onClick={() => setMenuOpen((v) => !v)}
            >
              {menuOpen ? <X size={18} /> : <Menu size={18} />}
            </button>
          </div>
        </div>
      </div>

      {/* ── Mobile menu ── */}
      {menuOpen && (
        <div className="md:hidden bg-surface-950/98 backdrop-blur-xl border-t border-white/8 px-4 py-4 space-y-1">
          {/* Mobile search */}
          <form
            onSubmit={handleSearch}
            className="flex items-center gap-2 bg-white/8 border border-white/12 rounded-xl px-3 py-2.5 mb-3"
          >
            <Search size={14} className="text-slate-500" />
            <input
              type="text"
              placeholder="Search PayFlex…"
              value={searchVal}
              onChange={(e) => setSearchVal(e.target.value)}
              className="bg-transparent outline-none flex-1 text-white placeholder-slate-500 text-sm"
            />
          </form>

          {NAV_LINKS.map(({ to, label }) => (
            <Link
              key={to}
              to={to}
              className={`block py-2.5 px-3 rounded-xl text-sm font-medium transition-colors ${
                isActive(to)
                  ? 'text-white bg-white/10'
                  : 'text-slate-400 hover:text-white hover:bg-white/8'
              }`}
            >
              {label}
            </Link>
          ))}
          {/* Mobile tags */}
          <div className="pt-1">
            <p className="text-[10px] font-bold uppercase tracking-widest text-slate-500 px-3 mb-2">
              Shop by Tags
            </p>
            <div className="flex flex-wrap gap-2 px-1">
              {tags.slice(0, 10).map((tag) => (
                <Link
                  key={tag.id}
                  to={`/tags/${tag.slug}`}
                  className="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-primary-500/15 border border-primary-400/30 text-primary-300 hover:bg-primary-500/25 transition-colors"
                >
                  {tag.name}
                </Link>
              ))}
            </div>
          </div>

          {!user && (
            <div className="pt-3 border-t border-white/8 mt-3 space-y-2">
              <Link
                to="/check-balance"
                className="flex items-center gap-2 py-2.5 px-3 rounded-xl text-sm text-slate-400 hover:text-white hover:bg-white/8 transition-colors"
              >
                <CreditCard size={14} /> Check Card Balance
              </Link>
              <div className="flex gap-2">
                <Link
                  to="/login"
                  className="flex-1 text-center py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-white/15 hover:bg-white/8 transition-colors"
                >
                  Login
                </Link>
                <Link
                  to="/register"
                  className="flex-1 text-center py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-primary-600 to-primary-500 text-white"
                >
                  Sign Up
                </Link>
              </div>
            </div>
          )}
          {user && (
            <div className="pt-3 border-t border-white/8 mt-3 space-y-1">
              <Link
                to="/profile"
                className="flex items-center gap-2 py-2.5 px-3 rounded-xl text-sm text-slate-400 hover:text-white hover:bg-white/8 transition-colors"
              >
                <User size={14} /> My Profile
              </Link>
              <Link
                to="/orders"
                className="flex items-center gap-2 py-2.5 px-3 rounded-xl text-sm text-slate-400 hover:text-white hover:bg-white/8 transition-colors"
              >
                <ShoppingBag size={14} /> My Orders
              </Link>
              <Link
                to="/my-points"
                className="flex items-center gap-2 py-2.5 px-3 rounded-xl text-sm text-amber-300 hover:text-amber-200 hover:bg-amber-500/10 transition-colors"
              >
                <Star size={14} /> My Points
                {pointBalance > 0 && (
                  <span className="ml-auto text-xs bg-amber-500/20 text-amber-300 px-2 py-0.5 rounded-full font-bold">
                    {pointBalance.toFixed(0)}
                  </span>
                )}
              </Link>
              <Link
                to="/check-balance"
                className="flex items-center gap-2 py-2.5 px-3 rounded-xl text-sm text-slate-400 hover:text-white hover:bg-white/8 transition-colors"
              >
                <CreditCard size={14} /> Check Balance
              </Link>
              <button
                onClick={handleLogout}
                className="flex items-center gap-2 w-full py-2.5 px-3 rounded-xl text-sm text-red-400 hover:bg-red-500/10 transition-colors"
              >
                <LogOut size={14} /> Sign Out
              </button>
            </div>
          )}
        </div>
      )}
    </header>
  )
}
