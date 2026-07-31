import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { ArrowRight, CreditCard } from 'lucide-react'
import { searchProducts } from '../../api/products'
import ProductCard, { PRODUCT_GRID_CLASS } from '../ui/ProductCard'
import { SkeletonCard } from '../ui/Skeleton'

export default function GiftCardsBrowseSection() {
  const { data, isLoading } = useQuery({
    queryKey: ['gift-cards-preview'],
    queryFn: () =>
      searchProducts({
        per_page: 12,
        page: 1,
        sort: 'popular',
      }),
    staleTime: 120_000,
  })

  const products = data?.data?.data ?? []

  return (
    <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center gap-3">
          <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-500 to-violet-600 flex items-center justify-center shadow-sm flex-shrink-0">
            <CreditCard size={17} className="text-white" />
          </div>
          <div>
            <h2 className="text-[18px] font-extrabold text-slate-900 tracking-tight">
              Browse gift cards
            </h2>
            <p className="text-xs text-slate-400 mt-0.5">Popular brands and denominations</p>
          </div>
        </div>
        <Link
          to="/gift-cards"
          className="flex items-center gap-1 text-sm font-bold text-primary-600 hover:text-primary-700 group transition-colors"
        >
          View all{' '}
          <ArrowRight size={13} className="group-hover:translate-x-0.5 transition-transform" />
        </Link>
      </div>

      <div className={PRODUCT_GRID_CLASS}>
        {isLoading
          ? Array.from({ length: 8 }).map((_, i) => <SkeletonCard key={i} />)
          : products.slice(0, 8).map((p) => <ProductCard key={p.id} product={p} />)}
        {!isLoading && products.length === 0 && (
          <p className="col-span-full text-slate-400 text-sm py-8">
            No gift cards available yet. Try again after catalog sync.
          </p>
        )}
      </div>
    </section>
  )
}
