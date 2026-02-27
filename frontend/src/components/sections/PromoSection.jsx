import { Link } from 'react-router-dom'
import { Gift, ArrowRight } from 'lucide-react'

const PROMOS = [
  {
    title: 'Earn PayFlex Points',
    desc: 'Get up to 2% back as PayFlex Points on every eligible transaction. 1 Point = ₹1.',
    cta: 'Learn More',
    link: '/loyalty',
    gradient: 'from-violet-500 to-purple-600',
    emoji: '⭐',
  },
  {
    title: 'Exclusive Hot Deals',
    desc: 'Save big on top brands. New deals every day — earn points on top of discounts!',
    cta: 'See Deals',
    link: '/hot-deals',
    gradient: 'from-red-500 to-rose-600',
    emoji: '🔥',
  },
  {
    title: '500+ Top Brands',
    desc: 'From daily essentials to lifestyle, entertainment & travel — all on PayFlex.',
    cta: 'Explore',
    link: '/categories',
    gradient: 'from-emerald-500 to-teal-600',
    emoji: '🎁',
  },
]

export default function PromoSection() {
  return (
    <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {PROMOS.map(({ title, desc, cta, link, gradient, emoji }) => (
          <div key={title} className={`relative overflow-hidden rounded-2xl bg-gradient-to-br ${gradient} p-6 text-white flex flex-col gap-3`}>
            <div className="absolute -right-4 -top-4 text-7xl opacity-20 select-none">{emoji}</div>
            <div className="text-3xl relative">{emoji}</div>
            <h3 className="text-lg font-bold leading-snug relative">{title}</h3>
            <p className="text-sm text-white/75 leading-relaxed relative">{desc}</p>
            <Link to={link} className="relative inline-flex items-center gap-1.5 mt-auto bg-white/20 hover:bg-white/30 border border-white/30 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors w-fit">
              {cta} <ArrowRight size={14} />
            </Link>
          </div>
        ))}
      </div>
    </section>
  )
}
