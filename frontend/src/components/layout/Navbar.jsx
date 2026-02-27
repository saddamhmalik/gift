import { useState, useEffect } from 'react'
import { Link, useNavigate, useLocation } from 'react-router-dom'
import { Gift, Search, User, LogOut, ChevronDown, Menu, X } from 'lucide-react'
import { useAuth } from '../../contexts/AuthContext'

export default function Navbar() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()
  const [scrolled, setScrolled]       = useState(false)
  const [menuOpen, setMenuOpen]       = useState(false)
  const [userMenuOpen, setUserMenuOpen] = useState(false)
  const [searchVal, setSearchVal]     = useState('')

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 10)
    window.addEventListener('scroll', onScroll, { passive: true })
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  useEffect(() => { setMenuOpen(false) }, [location.pathname])

  const handleSearch = (e) => {
    e.preventDefault()
    if (searchVal.trim()) navigate(`/search?q=${encodeURIComponent(searchVal.trim())}`)
  }

  const handleLogout = async () => {
    await logout()
    setUserMenuOpen(false)
    navigate('/')
  }

  return (
    <header className={`fixed inset-x-0 top-0 z-50 transition-all duration-300 ${scrolled ? 'bg-white/95 backdrop-blur-md shadow-md' : 'bg-white shadow-sm'}`}>
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">

          {/* Logo */}
          <Link to="/" className="flex items-center gap-2 flex-shrink-0">
            <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center shadow-sm">
              <Gift className="w-4.5 h-4.5 text-white" size={18} />
            </div>
            <span className="text-xl font-bold text-gray-900">
              Pay<span className="text-primary-500">Flex</span>
            </span>
          </Link>

          {/* Desktop nav */}
          <nav className="hidden md:flex items-center gap-6 text-sm font-medium text-gray-600">
            <Link to="/categories" className="hover:text-primary-500 transition-colors">Categories</Link>
            <Link to="/hot-deals"  className="hover:text-primary-500 transition-colors text-red-500 font-semibold">🔥 Hot Deals</Link>
            <Link to="/trending"   className="hover:text-primary-500 transition-colors">Trending</Link>
            <Link to="/new-arrivals" className="hover:text-primary-500 transition-colors">New Arrivals</Link>
          </nav>

          {/* Right: search + auth */}
          <div className="flex items-center gap-3">
            {/* Search */}
            <form onSubmit={handleSearch} className="hidden sm:flex items-center gap-2 bg-gray-100 rounded-xl px-3 py-2 text-sm focus-within:ring-2 focus-within:ring-primary-400 transition-all w-44 lg:w-64">
              <Search size={15} className="text-gray-400 flex-shrink-0" />
              <input
                type="text"
                placeholder="Search PayFlex…"
                value={searchVal}
                onChange={e => setSearchVal(e.target.value)}
                className="bg-transparent outline-none flex-1 placeholder-gray-400 text-gray-700"
              />
            </form>

            {/* Auth */}
            {user ? (
              <div className="relative">
                <button
                  onClick={() => setUserMenuOpen(v => !v)}
                  className="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-primary-500 transition-colors"
                >
                  <div className="w-8 h-8 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                    {(user.first_name?.[0] || user.name?.[0] || 'U').toUpperCase()}
                  </div>
                  <span className="hidden lg:inline">{user.first_name || user.name?.split(' ')[0]}</span>
                  <ChevronDown size={14} />
                </button>
                {userMenuOpen && (
                  <div className="absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                    <Link to="/profile" className="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" onClick={() => setUserMenuOpen(false)}>
                      <User size={14} /> My Account
                    </Link>
                    <Link to="/orders" className="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" onClick={() => setUserMenuOpen(false)}>
                      <Gift size={14} /> My Orders
                    </Link>

                    <hr className="my-1 border-gray-100" />
                    <button onClick={handleLogout} className="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-500 hover:bg-red-50">
                      <LogOut size={14} /> Logout
                    </button>
                  </div>
                )}
              </div>
            ) : (
              <div className="hidden sm:flex items-center gap-2">
                <Link to="/login"    className="btn-secondary !px-4 !py-2 !text-xs">Login</Link>
                <Link to="/register" className="btn-primary !px-4 !py-2 !text-xs">Sign Up</Link>
              </div>
            )}

            {/* Mobile menu toggle */}
            <button className="md:hidden p-1.5 rounded-lg text-gray-600 hover:bg-gray-100" onClick={() => setMenuOpen(v => !v)}>
              {menuOpen ? <X size={20} /> : <Menu size={20} />}
            </button>
          </div>
        </div>
      </div>

      {/* Mobile menu */}
      {menuOpen && (
        <div className="md:hidden bg-white border-t border-gray-100 px-4 py-4 space-y-2 shadow-lg">
          <form onSubmit={handleSearch} className="flex items-center gap-2 bg-gray-100 rounded-xl px-3 py-2 text-sm mb-3">
            <Search size={15} className="text-gray-400" />
            <input type="text" placeholder="Search PayFlex…" value={searchVal} onChange={e => setSearchVal(e.target.value)} className="bg-transparent outline-none flex-1 placeholder-gray-400" />
          </form>
          {[['/', 'Home'], ['/categories', 'Categories'], ['/hot-deals', '🔥 Hot Deals'], ['/trending', 'Trending'], ['/new-arrivals', 'New Arrivals']].map(([to, label]) => (
            <Link key={to} to={to} className="block py-2 px-3 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">{label}</Link>
          ))}
          {!user && (
            <div className="flex gap-2 pt-2">
              <Link to="/login"    className="btn-secondary flex-1 !justify-center !text-xs">Login</Link>
              <Link to="/register" className="btn-primary  flex-1 !justify-center !text-xs">Sign Up</Link>
            </div>
          )}
          {user && (
            <button onClick={handleLogout} className="flex items-center gap-2 w-full text-sm text-red-500 py-2 px-3 rounded-lg hover:bg-red-50">
              <LogOut size={14} /> Logout
            </button>
          )}
        </div>
      )}
    </header>
  )
}
