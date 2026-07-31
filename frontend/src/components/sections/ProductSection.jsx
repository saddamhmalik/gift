import { Link } from 'react-router-dom'
import { ArrowRight } from 'lucide-react'
import ProductCard, { PRODUCT_GRID_CLASS } from '../ui/ProductCard'
import { SkeletonCard } from '../ui/Skeleton'

export default function ProductSection({
  title,
  icon: Icon,
  iconColor = 'text-primary-500',
  badge,
  viewAllLink,
  products = [],
  isLoading,
  accentColor = 'from-primary-500 to-violet-500',
}) {
  const items = products.slice(0, 8)

  return (
    <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      {/* Header */}
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center gap-3">
          {Icon && (
            <div
              className={`w-9 h-9 rounded-xl bg-gradient-to-br ${accentColor} flex items-center justify-center shadow-sm flex-shrink-0`}
            >
              <Icon size={17} className="text-white" />
            </div>
          )}
          <h2 className="text-[18px] font-extrabold text-slate-900 tracking-tight">{title}</h2>
        </div>

        {viewAllLink && (
          <Link
            to={viewAllLink}
            className="flex items-center gap-1 text-sm font-bold text-primary-600 hover:text-primary-700 transition-colors group"
          >
            View all{' '}
            <ArrowRight size={13} className="group-hover:translate-x-0.5 transition-transform" />
          </Link>
        )}
      </div>

      {/* Grid — denser catalog layout (Woohoo / Gyftr style) */}
      <div className={PRODUCT_GRID_CLASS}>
        {isLoading
          ? Array.from({ length: 8 }).map((_, i) => <SkeletonCard key={i} />)
          : items.map((p) => <ProductCard key={p.id} product={p} badge={badge} />)}
        {!isLoading && products.length === 0 && (
          <p className="col-span-full text-slate-400 text-sm py-8">No products available.</p>
        )}
      </div>
    </section>
  )
}
