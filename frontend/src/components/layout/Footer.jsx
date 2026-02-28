import { Link } from 'react-router-dom'
import { Zap, Twitter, Instagram, Linkedin, Mail, MapPin, ExternalLink } from 'lucide-react'

const SHOP_LINKS = [
  ['Categories',   '/categories'],
  ['Hot Deals 🔥', '/hot-deals'],
  ['Trending',     '/trending'],
  ['Best Sellers', '/best-sellers'],
  ['New Arrivals', '/new-arrivals'],
  ['Featured',     '/featured'],
]

const ACCOUNT_LINKS = [
  ['Login',          '/login'],
  ['Sign Up',        '/register'],
  ['My Orders',      '/orders'],
  ['Check Balance',  '/check-balance'],
]

const COMPANY_LINKS = [
  ['About Us',           '/about'],
  ['Privacy Policy',     '/privacy-policy'],
  ['Terms & Conditions', '/terms'],
  ['Loyalty Program',    '/loyalty'],
]

export default function Footer() {
  return (
    <footer className="bg-surface-950 text-slate-500 mt-20">

      {/* Top gradient line */}
      <div className="h-px bg-gradient-to-r from-transparent via-primary-500/50 to-transparent" />

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-10">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-10">

          {/* ── Brand (2 cols) ── */}
          <div className="lg:col-span-2">
            <Link to="/" className="flex items-center gap-2.5 mb-5 group w-fit">
              <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-500 to-brand flex items-center justify-center shadow-glow-primary transition-transform group-hover:scale-105">
                <Zap size={17} className="text-white" />
              </div>
              <span className="text-xl font-extrabold text-white tracking-tight">
                Pay<span className="text-primary-400">Flex</span>
              </span>
            </Link>

            <p className="text-sm text-slate-500 leading-relaxed mb-6 max-w-[280px]">
              India's smart payments &amp; rewards platform. Earn PayFlex Points on every purchase and redeem for real savings — <span className="text-slate-400">1 Point = ₹1.</span>
            </p>

            {/* Social */}
            <div className="flex gap-2 mb-8">
              {[
                { Icon: Twitter,   href: '#', label: 'Twitter'   },
                { Icon: Instagram, href: '#', label: 'Instagram' },
                { Icon: Linkedin,  href: '#', label: 'LinkedIn'  },
              ].map(({ Icon, href, label }) => (
                <a key={label} href={href} aria-label={label}
                  className="w-8 h-8 rounded-lg bg-white/5 border border-white/8 flex items-center justify-center hover:bg-primary-500/20 hover:border-primary-500/40 hover:text-primary-400 transition-all">
                  <Icon size={14} />
                </a>
              ))}
            </div>

            {/* Contact */}
            <ul className="space-y-3 text-xs">
              <li className="flex items-start gap-2.5">
                <Mail size={13} className="text-primary-400 mt-0.5 flex-shrink-0" />
                <a href="mailto:info@payflex.in" className="hover:text-primary-400 transition-colors">info@payflex.in</a>
              </li>
              <li className="flex items-start gap-2.5">
                <MapPin size={13} className="text-primary-400 mt-0.5 flex-shrink-0" />
                <span>Unit No. 607, 6th Floor, Capital Business Park,<br />Sector-48, Sohna Road, Gurgaon — 122018</span>
              </li>
            </ul>
          </div>

          {/* ── Shop ── */}
          <div>
            <h4 className="text-white text-sm font-bold mb-4 tracking-wide uppercase text-[11px] opacity-60">Shop</h4>
            <ul className="space-y-2.5 text-sm">
              {SHOP_LINKS.map(([label, to]) => (
                <li key={to}>
                  <Link to={to} className="hover:text-white hover:translate-x-0.5 inline-flex transition-all duration-150">{label}</Link>
                </li>
              ))}
            </ul>
          </div>

          {/* ── Account ── */}
          <div>
            <h4 className="text-white text-sm font-bold mb-4 tracking-wide uppercase text-[11px] opacity-60">Account</h4>
            <ul className="space-y-2.5 text-sm">
              {ACCOUNT_LINKS.map(([label, to]) => (
                <li key={to}>
                  <Link to={to} className="hover:text-white hover:translate-x-0.5 inline-flex transition-all duration-150">{label}</Link>
                </li>
              ))}
            </ul>
          </div>

          {/* ── Company ── */}
          <div>
            <h4 className="text-white text-sm font-bold mb-4 tracking-wide uppercase text-[11px] opacity-60">Company</h4>
            <ul className="space-y-2.5 text-sm">
              {COMPANY_LINKS.map(([label, to]) => (
                <li key={to}>
                  <Link to={to} className="hover:text-white hover:translate-x-0.5 inline-flex transition-all duration-150">{label}</Link>
                </li>
              ))}
            </ul>
          </div>
        </div>
      </div>

      {/* Bottom bar */}
      <div className="border-t border-white/5 py-5">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
          <span className="text-slate-600">© {new Date().getFullYear()} SmartPayflex Payments Pvt. Ltd. All rights reserved.</span>
          <div className="flex items-center gap-4 text-slate-600">
            <Link to="/privacy-policy"  className="hover:text-slate-300 transition-colors">Privacy</Link>
            <Link to="/terms"           className="hover:text-slate-300 transition-colors">Terms</Link>
            <Link to="/loyalty"         className="hover:text-slate-300 transition-colors">Loyalty</Link>
            <Link to="/check-balance"   className="hover:text-slate-300 transition-colors">Check Balance</Link>
          </div>
        </div>
      </div>
    </footer>
  )
}
