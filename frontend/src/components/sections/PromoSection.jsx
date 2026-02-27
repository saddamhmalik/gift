import { Link } from 'react-router-dom'
import { ArrowRight } from 'lucide-react'

const PROMOS = [
  {
    title:    'Earn PayFlex Points',
    desc:     'Get up to 2% back as PayFlex Points on every eligible purchase. 1 Point = ₹1, redeemable instantly.',
    cta:      'Learn More',
    link:     '/loyalty',
    gradient: 'from-primary-600 via-violet-600 to-indigo-700',
    emoji:    '⭐',
    pattern:  'radial-gradient(circle at 80% 20%, rgba(255,255,255,0.12) 0%, transparent 60%)',
  },
  {
    title:    'Exclusive Hot Deals',
    desc:     'Save big on top brands — plus earn double points on select deals. New offers every day!',
    cta:      'See Deals',
    link:     '/hot-deals',
    gradient: 'from-rose-500 via-red-500 to-orange-600',
    emoji:    '🔥',
    pattern:  'radial-gradient(circle at 20% 80%, rgba(255,255,255,0.12) 0%, transparent 60%)',
  },
  {
    title:    '500+ Top Brands',
    desc:     'From daily essentials to travel, entertainment & lifestyle — everything on PayFlex.',
    cta:      'Explore All',
    link:     '/categories',
    gradient: 'from-emerald-500 via-teal-500 to-cyan-600',
    emoji:    '🎁',
    pattern:  'radial-gradient(circle at 50% 10%, rgba(255,255,255,0.12) 0%, transparent 60%)',
  },
]

export default function PromoSection() {
  return (
    <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {PROMOS.map(({ title, desc, cta, link, gradient, emoji, pattern }) => (
          <Link
            key={title}
            to={link}
            className="group relative overflow-hidden rounded-2xl p-6 flex flex-col gap-3 min-h-[180px]"
          >
            {/* Background */}
            <div className={`absolute inset-0 bg-gradient-to-br ${gradient}`} />
            {/* Radial overlay */}
            <div className="absolute inset-0" style={{ background: pattern }} />
            {/* Floating emoji */}
            <div className="absolute -right-3 -bottom-3 text-[80px] opacity-20 select-none">
              {emoji}
            </div>

            {/* Content */}
            <div className="relative z-10 flex flex-col gap-2 flex-1">
              <span className="text-4xl leading-none">{emoji}</span>
              <h3 className="text-base font-extrabold text-white leading-tight">{title}</h3>
              <p className="text-sm text-white/75 leading-relaxed flex-1">{desc}</p>
              <div className="flex items-center gap-1 text-white/90 font-bold text-sm mt-1 group-hover:gap-2 transition-all">
                {cta} <ArrowRight size={13} className="group-hover:translate-x-0.5 transition-transform" />
              </div>
            </div>
          </Link>
        ))}
      </div>
    </section>
  )
}
