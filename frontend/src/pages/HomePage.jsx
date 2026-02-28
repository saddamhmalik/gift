import { useQuery } from '@tanstack/react-query'
import { Flame, TrendingUp, Star, Sparkles, Package, Tag, ArrowRight } from 'lucide-react'
import { Link } from 'react-router-dom'
import HeroSection       from '../components/sections/HeroSection'
import CategoriesSection from '../components/sections/CategoriesSection'
import ProductSection    from '../components/sections/ProductSection'
import PromoSection      from '../components/sections/PromoSection'
import TagsSection       from '../components/sections/TagsSection'
import { getHotDeals, getTrending, getBestSellers, getFeatured, getNewArrivals } from '../api/products'
import { getTags } from '../api/tags'

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

/** Full-width "Shop by Tags" section shown at the bottom of the homepage */
function TagsBrowseSection() {
  const { data, isLoading } = useQuery({
    queryKey: ['tags'],
    queryFn:  getTags,
    staleTime: 300_000,
  })

  const tags = data?.data ?? []
  if (!isLoading && tags.length === 0) return null

  return (
    <section className="bg-white border-t border-slate-100 py-14">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {/* Header */}
        <div className="flex items-center justify-between mb-8">
          <div className="flex items-center gap-3">
            <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-500 to-violet-600 flex items-center justify-center shadow-sm">
              <Tag size={16} className="text-white" />
            </div>
            <div>
              <h2 className="text-[18px] font-extrabold text-slate-900 tracking-tight">Shop by Tags</h2>
              <p className="text-xs text-slate-400 mt-0.5">Browse gifts by category or occasion</p>
            </div>
          </div>
          <Link
            to="/tags"
            className="flex items-center gap-1 text-sm font-bold text-primary-600 hover:text-primary-700 group transition-colors"
          >
            All tags <ArrowRight size={13} className="group-hover:translate-x-0.5 transition-transform" />
          </Link>
        </div>

        {/* Tag grid */}
        <div className="flex flex-wrap gap-3">
          {isLoading
            ? Array.from({ length: 12 }).map((_, i) => (
                <div key={i} className="skeleton h-10 rounded-full" style={{ width: `${80 + (i % 4) * 24}px` }} />
              ))
            : tags.map(tag => (
                <Link
                  key={tag.id}
                  to={`/tags/${tag.slug}`}
                  className="group inline-flex items-center gap-2 px-5 py-2.5 rounded-full border border-slate-200 bg-slate-50 text-slate-700 text-sm font-semibold hover:bg-primary-50 hover:border-primary-200 hover:text-primary-700 hover:-translate-y-0.5 transition-all duration-200 shadow-sm"
                >
                  <span className="w-1.5 h-1.5 rounded-full bg-slate-400 group-hover:bg-primary-500 flex-shrink-0 transition-colors" />
                  {tag.name}
                  {tag.products_count > 0 && (
                    <span className="text-[11px] font-normal text-slate-400 group-hover:text-primary-400 transition-colors">
                      {tag.products_count}
                    </span>
                  )}
                </Link>
              ))
          }
        </div>
      </div>
    </section>
  )
}

export default function HomePage() {
  const queries = {}
  for (const { key, fn } of SECTIONS) {
    // eslint-disable-next-line react-hooks/rules-of-hooks
    queries[key] = useProductQuery(key, fn)
  }

  return (
    <>
      <HeroSection />

      {/* Categories + Tags chips + Promos */}
      <div className="bg-white">
        <CategoriesSection />
        <TagsSection />
        <PromoSection />
      </div>

      {/* Product sections — only rendered when they have at least one product */}
      <div className="bg-surface-50">
        {SECTIONS.map(({ key, title, icon, iconColor, badge, viewAllLink, accentColor }) => {
          const q = queries[key]
          const products = q.data?.data ?? []
          // Skip section entirely once loaded with no results
          if (!q.isLoading && products.length === 0) return null
          return (
            <ProductSection
              key={key}
              title={title}
              icon={icon}
              iconColor={iconColor}
              badge={badge}
              viewAllLink={viewAllLink}
              products={products}
              isLoading={q.isLoading}
              accentColor={accentColor}
            />
          )
        })}
      </div>

      {/* Shop by Tags — full browse section */}
      <TagsBrowseSection />

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
