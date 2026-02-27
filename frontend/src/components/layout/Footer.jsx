import { Link } from 'react-router-dom'
import { Zap, Twitter, Instagram, Facebook, Mail, MapPin } from 'lucide-react'

const SHOP_LINKS = [
  ['Categories',   '/categories'],
  ['Hot Deals',    '/hot-deals'],
  ['Trending',     '/trending'],
  ['Best Sellers', '/best-sellers'],
  ['New Arrivals', '/new-arrivals'],
  ['Featured',     '/featured'],
]

const ACCOUNT_LINKS = [
  ['Login',     '/login'],
  ['Register',  '/register'],
  ['My Orders', '/orders'],
]

const COMPANY_LINKS = [
  ['About Us',             '/about'],
  ['Privacy Policy',       '/privacy-policy'],
  ['Terms & Conditions',   '/terms'],
  ['Loyalty Program',      '/loyalty'],
]

export default function Footer() {
  return (
    <footer className="bg-gray-950 text-gray-400 mt-24">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-10">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-10">

          {/* Brand — takes 2 columns */}
          <div className="lg:col-span-2">
            <Link to="/" className="flex items-center gap-2 mb-5">
              <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-500 to-orange-500 flex items-center justify-center shadow-lg">
                <Zap size={18} className="text-white" />
              </div>
              <span className="text-xl font-extrabold text-white tracking-tight">
                Pay<span className="text-primary-400">Flex</span>
              </span>
            </Link>
            <p className="text-sm text-gray-500 leading-relaxed mb-5 max-w-xs">
              India's smart payments &amp; rewards platform. Earn PayFlex Points on every transaction and redeem them for real savings — 1 Point = ₹1.
            </p>

            {/* Social */}
            <div className="flex gap-2.5 mb-8">
              {[Twitter, Instagram, Facebook].map((Icon, i) => (
                <a key={i} href="#" aria-label="social" className="w-8 h-8 rounded-lg bg-gray-800 flex items-center justify-center hover:bg-primary-500 transition-colors">
                  <Icon size={14} />
                </a>
              ))}
            </div>

            {/* Contact */}
            <ul className="space-y-2.5 text-xs">
              <li className="flex items-start gap-2">
                <Mail size={13} className="text-primary-400 mt-0.5 flex-shrink-0" />
                <a href="mailto:info@payflex.in" className="hover:text-primary-400 transition-colors">info@payflex.in</a>
              </li>
              <li className="flex items-start gap-2">
                <MapPin size={13} className="text-primary-400 mt-0.5 flex-shrink-0" />
                <span>Unit No. 607, 6th Floor, Capital Business Park, Sector-48, Sohna Road, Gurgaon, Haryana — 122018</span>
              </li>
            </ul>
          </div>

          {/* Shop */}
          <div>
            <h4 className="text-white text-sm font-bold mb-4">Shop</h4>
            <ul className="space-y-2.5 text-sm">
              {SHOP_LINKS.map(([label, to]) => (
                <li key={to}><Link to={to} className="hover:text-primary-400 transition-colors">{label}</Link></li>
              ))}
            </ul>
          </div>

          {/* Account */}
          <div>
            <h4 className="text-white text-sm font-bold mb-4">Account</h4>
            <ul className="space-y-2.5 text-sm">
              {ACCOUNT_LINKS.map(([label, to]) => (
                <li key={to}><Link to={to} className="hover:text-primary-400 transition-colors">{label}</Link></li>
              ))}
            </ul>
          </div>

          {/* Company */}
          <div>
            <h4 className="text-white text-sm font-bold mb-4">Company</h4>
            <ul className="space-y-2.5 text-sm">
              {COMPANY_LINKS.map(([label, to]) => (
                <li key={to}><Link to={to} className="hover:text-primary-400 transition-colors">{label}</Link></li>
              ))}
            </ul>
          </div>

        </div>
      </div>

      {/* Bottom bar */}
      <div className="border-t border-gray-800/60 py-5">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-600">
          <span>© {new Date().getFullYear()} SmartPayflex Payments Pvt. Ltd. All rights reserved.</span>
          <div className="flex items-center gap-4">
            <Link to="/privacy-policy" className="hover:text-gray-400 transition-colors">Privacy</Link>
            <Link to="/terms"          className="hover:text-gray-400 transition-colors">Terms</Link>
            <Link to="/loyalty"        className="hover:text-gray-400 transition-colors">Loyalty</Link>
          </div>
        </div>
      </div>
    </footer>
  )
}
