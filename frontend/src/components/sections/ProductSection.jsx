import { useRef } from 'react'
import { Link } from 'react-router-dom'
import { ChevronLeft, ChevronRight, ArrowRight } from 'lucide-react'
import ProductCard from '../ui/ProductCard'
import { SkeletonCard } from '../ui/Skeleton'

export default function ProductSection({ title, icon: Icon, iconColor = 'text-primary-500', badge, viewAllLink, products = [], isLoading, accentClass = '' }) {
  const scrollRef = useRef(null)

  const scroll = (dir) => {
    if (scrollRef.current) {
      scrollRef.current.scrollBy({ left: dir * 260, behavior: 'smooth' })
    }
  }

  return (
    <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      {/* Header */}
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center gap-3">
          {accentClass && <div className={`w-1 h-7 rounded-full ${accentClass}`} />}
          <div>
            <h2 className="section-title flex items-center gap-2">
              {Icon && <Icon size={22} className={iconColor} />}
              {title}
            </h2>
          </div>
        </div>
        <div className="flex items-center gap-2">
          <button onClick={() => scroll(-1)} className="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center shadow-sm hover:bg-gray-50 transition-colors">
            <ChevronLeft size={16} />
          </button>
          <button onClick={() => scroll(1)} className="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center shadow-sm hover:bg-gray-50 transition-colors">
            <ChevronRight size={16} />
          </button>
          {viewAllLink && (
            <Link to={viewAllLink} className="ml-2 text-sm font-semibold text-primary-500 hover:text-primary-600 flex items-center gap-1 transition-colors">
              View all <ArrowRight size={14} />
            </Link>
          )}
        </div>
      </div>

      {/* Scroll track */}
      <div
        ref={scrollRef}
        className="flex gap-4 overflow-x-auto pb-3 -mx-1 px-1 scrollbar-none"
        style={{ scrollSnapType: 'x mandatory', msOverflowStyle: 'none', scrollbarWidth: 'none' }}
      >
        {isLoading
          ? Array.from({length: 5}).map((_, i) => (
              <div key={i} className="flex-shrink-0 w-56 sm:w-60" style={{scrollSnapAlign:'start'}}>
                <SkeletonCard />
              </div>
            ))
          : products.map(p => (
              <div key={p.id} className="flex-shrink-0 w-56 sm:w-60" style={{scrollSnapAlign:'start'}}>
                <ProductCard product={p} badge={badge} />
              </div>
            ))
        }
        {!isLoading && products.length === 0 && (
          <p className="text-gray-400 text-sm py-8">No products available.</p>
        )}
      </div>
    </section>
  )
}
