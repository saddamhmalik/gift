import { useQuery } from '@tanstack/react-query'
import { Flame, TrendingUp, Star, Sparkles, Package } from 'lucide-react'
import HeroSection       from '../components/sections/HeroSection'
import CategoriesSection from '../components/sections/CategoriesSection'
import ProductSection    from '../components/sections/ProductSection'
import PromoSection      from '../components/sections/PromoSection'
import TagsSection       from '../components/sections/TagsSection'
import { getHotDeals, getTrending, getBestSellers, getFeatured, getNewArrivals } from '../api/products'

function useProductQuery(key, fn) {
  return useQuery({ queryKey: [key], queryFn: fn, staleTime: 300_000 })
}

const SECTIONS = [
  { key: 'hotDeals',    fn: getHotDeals,    title: 'Hot Deals',    icon: Flame,      iconColor: 'text-rose-500',    badge: 'deal',       viewAllLink: '/hot-deals',    accentColor: 'from-rose-500 to-orange-500' },
  { key: 'trending',    fn: getTrending,    title: 'Trending Now', icon: TrendingUp, iconColor: 'text-primary-500', badge: 'trending',   viewAllLink: '/trending',     accentColor: 'from-primary-500 to-violet-600' },
  { key: 'bestSellers', fn: getBestSellers, title: 'Best Sellers', icon: Star,       iconColor: 'text-amber-500',   badge: 'bestseller', viewAllLink: '/best-sellers', accentColor: 'from-amber-400 to-orange-500' },
  { key: 'featured',    fn: getFeatured,    title: 'Featured',     icon: Sparkles,   iconColor: 'text-blue-500',    badge: 'featured',   viewAllLink: '/featured',     accentColor: 'from-blue-500 to-indigo-600' },
  { key: 'newArrivals', fn: getNewArrivals, title: 'New Arrivals', icon: Package,    iconColor: 'text-emerald-500', badge: 'new',        viewAllLink: '/new-arrivals', accentColor: 'from-emerald-500 to-teal-600' },
]

export default function HomePage() {
  const queries = {}
  for (const { key, fn } of SECTIONS) {
    // eslint-disable-next-line react-hooks/rules-of-hooks
    queries[key] = useProductQuery(key, fn)
  }

  return (
    <>
      <HeroSection />

      {/* Categories + Tags + Promos */}
      <div className="bg-white">
        <CategoriesSection />
        <TagsSection />
        <PromoSection />
      </div>

      {/* Product sections with alternating subtle tint */}
      <div className="bg-surface-50">
        {SECTIONS.map(({ key, title, icon, iconColor, badge, viewAllLink, accentColor }) => (
          <ProductSection
            key={key}
            title={title}
            icon={icon}
            iconColor={iconColor}
            badge={badge}
            viewAllLink={viewAllLink}
            products={queries[key].data?.data ?? []}
            isLoading={queries[key].isLoading}
            accentColor={accentColor}
          />
        ))}
      </div>

      {/* Trust bar */}
      <section className="bg-surface-950 py-14">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            {[
              { num: '500+', label: 'Gift Card Brands',  emoji: '🏷️' },
              { num: '50K+', label: 'Happy Customers',   emoji: '😊' },
              { num: '100%', label: 'Secure Payments',   emoji: '🔒' },
              { num: '24/7', label: 'Instant Delivery',  emoji: '⚡' },
            ].map(({ num, label, emoji }) => (
              <div key={label} className="flex flex-col items-center gap-2.5">
                <span className="text-4xl">{emoji}</span>
                <span className="text-3xl font-extrabold text-white">{num}</span>
                <span className="text-sm text-slate-500 font-medium">{label}</span>
              </div>
            ))}
          </div>
        </div>
      </section>
    </>
  )
}
