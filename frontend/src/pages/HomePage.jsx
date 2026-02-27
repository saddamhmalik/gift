import { useQuery } from '@tanstack/react-query'
import { Flame, TrendingUp, Star, Sparkles, Package } from 'lucide-react'
import HeroSection      from '../components/sections/HeroSection'
import CategoriesSection from '../components/sections/CategoriesSection'
import ProductSection   from '../components/sections/ProductSection'
import PromoSection     from '../components/sections/PromoSection'
import { getHotDeals, getTrending, getBestSellers, getFeatured, getNewArrivals } from '../api/products'

function useProductQuery(key, fn) {
  return useQuery({ queryKey: [key], queryFn: fn, staleTime: 1000 * 60 * 5 })
}

export default function HomePage() {
  const hotDeals    = useProductQuery('hotDeals',    getHotDeals)
  const trending    = useProductQuery('trending',    getTrending)
  const bestSellers = useProductQuery('bestSellers', getBestSellers)
  const featured    = useProductQuery('featured',    getFeatured)
  const newArrivals = useProductQuery('newArrivals', getNewArrivals)

  return (
    <>
      <HeroSection />
      <CategoriesSection />
      <PromoSection />

      <ProductSection
        title="Hot Deals"
        icon={Flame}
        iconColor="text-red-500"
        badge="deal"
        viewAllLink="/hot-deals"
        products={hotDeals.data?.data ?? []}
        isLoading={hotDeals.isLoading}
        accentClass="bg-red-500"
      />

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="border-t border-gray-100" />
      </div>

      <ProductSection
        title="Trending Now"
        icon={TrendingUp}
        iconColor="text-purple-500"
        badge="trending"
        viewAllLink="/trending"
        products={trending.data?.data ?? []}
        isLoading={trending.isLoading}
        accentClass="bg-purple-500"
      />

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="border-t border-gray-100" />
      </div>

      <ProductSection
        title="Best Sellers"
        icon={Star}
        iconColor="text-amber-500"
        badge="bestseller"
        viewAllLink="/best-sellers"
        products={bestSellers.data?.data ?? []}
        isLoading={bestSellers.isLoading}
        accentClass="bg-amber-500"
      />

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="border-t border-gray-100" />
      </div>

      <ProductSection
        title="Featured"
        icon={Sparkles}
        iconColor="text-blue-500"
        badge="featured"
        viewAllLink="/featured"
        products={featured.data?.data ?? []}
        isLoading={featured.isLoading}
        accentClass="bg-blue-500"
      />

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="border-t border-gray-100" />
      </div>

      <ProductSection
        title="New Arrivals"
        icon={Package}
        iconColor="text-green-500"
        badge="new"
        viewAllLink="/new-arrivals"
        products={newArrivals.data?.data ?? []}
        isLoading={newArrivals.isLoading}
        accentClass="bg-green-500"
      />

      {/* Trust bar */}
      <section className="bg-gradient-to-r from-primary-50 to-amber-50 border-y border-primary-100 py-10 mt-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            {[
              { num: '500+', label: 'Gift Card Brands',  emoji: '🏷️' },
              { num: '50K+', label: 'Happy Customers',   emoji: '😊' },
              { num: '100%', label: 'Secure Payments',   emoji: '🔒' },
              { num: '24/7', label: 'Customer Support',  emoji: '💬' },
            ].map(({ num, label, emoji }) => (
              <div key={label} className="flex flex-col items-center gap-2">
                <span className="text-3xl">{emoji}</span>
                <span className="text-2xl font-extrabold text-gray-900">{num}</span>
                <span className="text-sm text-gray-500 font-medium">{label}</span>
              </div>
            ))}
          </div>
        </div>
      </section>
    </>
  )
}
