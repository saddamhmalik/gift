import { useQuery } from '@tanstack/react-query'
import { useLocation } from 'react-router-dom'
import { Flame, TrendingUp, Star, Sparkles, Package } from 'lucide-react'
import {
  getHotDeals,
  getTrending,
  getBestSellers,
  getFeatured,
  getNewArrivals,
} from '../api/products'
import ProductCard from '../components/ui/ProductCard'
import { SkeletonCard } from '../components/ui/Skeleton'

const CONFIG = {
  '/hot-deals': {
    title: 'Hot Deals',
    fn: getHotDeals,
    badge: 'deal',
    Icon: Flame,
    color: 'text-red-500',
    desc: 'Exclusive discounts on top gift cards',
  },
  '/trending': {
    title: 'Trending',
    fn: getTrending,
    badge: 'trending',
    Icon: TrendingUp,
    color: 'text-purple-500',
    desc: 'What everyone is buying right now',
  },
  '/best-sellers': {
    title: 'Best Sellers',
    fn: getBestSellers,
    badge: 'bestseller',
    Icon: Star,
    color: 'text-amber-500',
    desc: 'Our most popular gift cards',
  },
  '/featured': {
    title: 'Featured',
    fn: getFeatured,
    badge: 'featured',
    Icon: Sparkles,
    color: 'text-blue-500',
    desc: 'Hand-picked gift cards by our team',
  },
  '/new-arrivals': {
    title: 'New Arrivals',
    fn: getNewArrivals,
    badge: 'new',
    Icon: Package,
    color: 'text-green-500',
    desc: 'Freshly added gift card brands',
  },
}

export default function ProductListPage() {
  const { pathname } = useLocation()
  const cfg = CONFIG[pathname] ?? CONFIG['/trending']
  const { Icon } = cfg

  const { data, isLoading } = useQuery({
    queryKey: [pathname],
    queryFn: cfg.fn,
    staleTime: 1000 * 60 * 5,
  })

  const products = data?.data ?? []

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div className="mb-8">
        <h1 className="text-3xl font-extrabold text-gray-900 flex items-center gap-2">
          <Icon size={28} className={cfg.color} /> {cfg.title}
        </h1>
        <p className="text-gray-500 mt-1">{cfg.desc}</p>
      </div>

      <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        {isLoading
          ? Array.from({ length: 10 }).map((_, i) => <SkeletonCard key={i} />)
          : products.map((p) => <ProductCard key={p.id} product={p} badge={cfg.badge} />)}
      </div>

      {!isLoading && products.length === 0 && (
        <div className="text-center py-16 text-gray-400">
          <div className="text-5xl mb-3">📭</div>
          <p>No products available right now. Check back soon!</p>
        </div>
      )}
    </div>
  )
}
