import { useState } from 'react'
import { Link } from 'react-router-dom'

/** Shared product catalog grid — keep identical on homepage & listing pages */
export const PRODUCT_GRID_CLASS =
  'grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4'

/** Same card size as homepage when a sidebar reduces content width */
export const PRODUCT_GRID_CLASS_WITH_SIDEBAR =
  'grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4'

export default function ProductCard({ product }) {
  const [imgError, setImgError] = useState(false)

  const imgSrc = product.image_url || product.thumbnail_url
  const showImg = imgSrc && !imgError

  return (
    <Link
      to={`/products/${product.slug || product.id}`}
      className="group flex flex-col bg-white rounded-2xl border border-slate-200/90 overflow-hidden h-full transition-all duration-300 hover:-translate-y-1 hover:border-slate-300 hover:shadow-[0_12px_40px_-12px_rgba(15,23,42,0.18)]"
    >
      <div className="relative bg-slate-100 border-b border-slate-100 overflow-hidden">
        {showImg ? (
          <img
            src={imgSrc}
            alt={product.name}
            onError={() => setImgError(true)}
            className="w-full h-auto block group-hover:scale-[1.03] transition-transform duration-500 ease-out origin-center"
            loading="lazy"
            decoding="async"
          />
        ) : (
          <div className="flex items-center justify-center h-36 px-4 bg-slate-100">
            <p className="text-slate-500 font-semibold text-sm text-center line-clamp-3">
              {product.name}
            </p>
          </div>
        )}
      </div>

      <div className="flex items-center justify-between gap-2 px-3.5 sm:px-4 py-2.5">
        <h3 className="min-w-0 flex-1 text-[13px] sm:text-sm font-semibold text-slate-900 leading-snug truncate group-hover:text-primary-700 transition-colors">
          {product.name}
        </h3>
        <span className="flex-shrink-0 text-[11px] font-semibold text-primary-600 group-hover:text-primary-700 transition-colors">
          Buy →
        </span>
      </div>
    </Link>
  )
}
