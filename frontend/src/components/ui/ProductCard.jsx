import { Link } from 'react-router-dom'
import { Flame, TrendingUp, Star, Sparkles, Tag, ArrowRight } from 'lucide-react'

const BADGE_MAP = {
  deal:       { label: 'Hot Deal',    cls: 'from-red-500 to-orange-500',    Icon: Flame },
  trending:   { label: 'Trending',    cls: 'from-purple-500 to-violet-600', Icon: TrendingUp },
  bestseller: { label: 'Best Seller', cls: 'from-amber-400 to-orange-500',  Icon: Star },
  featured:   { label: 'Featured',    cls: 'from-blue-500 to-indigo-600',   Icon: Sparkles },
  new:        { label: 'New',         cls: 'from-emerald-400 to-teal-500',  Icon: Tag },
}

export default function ProductCard({ product, badge }) {
  const badgeInfo = badge ? BADGE_MAP[badge] : null

  const minP = parseFloat(product.min_price) || 0
  const maxP = parseFloat(product.max_price) || 0
  const dealP = product.is_on_deal && product.deal_price ? parseFloat(product.deal_price) : null
  const currency = product.currency_code ?? 'INR'

  const savePct = dealP && minP ? Math.round((1 - dealP / minP) * 100) : null

  // Show up to 3 denomination chips if available, otherwise show price range
  const denoms = Array.isArray(product.denominations) ? product.denominations : []
  const showDenomChips = denoms.length > 0 && denoms.length <= 6
  const denomsToShow = denoms.slice(0, 4)
  const remaining = denoms.length - denomsToShow.length

  return (
    <Link
      to={`/products/${product.slug || product.id}`}
      className="group flex flex-col bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
    >
      {/* ── Image ── */}
      <div className="relative overflow-hidden bg-gradient-to-br from-slate-50 to-gray-100" style={{ aspectRatio: '4/3' }}>
        {product.thumbnail_url || product.image_url ? (
          <img
            src={product.thumbnail_url || product.image_url}
            alt={product.name}
            className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
            loading="lazy"
          />
        ) : (
          <div className="w-full h-full flex items-center justify-center">
            <span className="text-5xl opacity-60 group-hover:scale-110 transition-transform duration-300">🎁</span>
          </div>
        )}

        {/* Gradient overlay for readability */}
        <div className="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />

        {/* Category pill — top left */}
        {product.category?.name && (
          <div className="absolute top-2.5 left-2.5">
            <span className="text-[10px] font-semibold uppercase tracking-wider bg-white/90 backdrop-blur-sm text-gray-600 px-2 py-0.5 rounded-full shadow-sm">
              {product.category.name}
            </span>
          </div>
        )}

        {/* Type badge — top right */}
        {badgeInfo && (
          <div className={`absolute top-2.5 right-2.5 bg-gradient-to-r ${badgeInfo.cls} text-white text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1 shadow-md`}>
            <badgeInfo.Icon size={9} />
            {badgeInfo.label}
          </div>
        )}

        {/* Discount % — bottom right of image */}
        {savePct > 0 && (
          <div className="absolute bottom-2.5 right-2.5 bg-red-500 text-white text-[11px] font-extrabold px-2 py-0.5 rounded-full shadow">
            -{savePct}% OFF
          </div>
        )}
      </div>

      {/* ── Body ── */}
      <div className="p-3.5 flex flex-col flex-1 gap-2">
        {/* Name */}
        <h3 className="text-sm font-bold text-gray-800 line-clamp-2 leading-snug group-hover:text-primary-600 transition-colors duration-200">
          {product.name}
        </h3>

        {/* Short description */}
        {product.offer_short_desc && (
          <p className="text-[11px] text-gray-400 line-clamp-1 leading-relaxed">{product.offer_short_desc}</p>
        )}

        {/* Denomination chips OR price range */}
        {showDenomChips ? (
          <div className="flex flex-wrap gap-1 mt-0.5">
            {denomsToShow.map((d) => (
              <span
                key={d}
                className="text-[10px] font-semibold bg-primary-50 text-primary-600 border border-primary-100 px-1.5 py-0.5 rounded-md"
              >
                {currency} {Number(d).toLocaleString()}
              </span>
            ))}
            {remaining > 0 && (
              <span className="text-[10px] font-semibold bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded-md">
                +{remaining} more
              </span>
            )}
          </div>
        ) : (
          <div className="flex items-baseline gap-1.5 mt-0.5">
            {dealP ? (
              <>
                <span className="text-sm font-extrabold text-red-500">{currency} {dealP.toLocaleString()}</span>
                <span className="text-xs text-gray-400 line-through">{currency} {minP.toLocaleString()}</span>
              </>
            ) : (
              <span className="text-sm font-bold text-gray-800">
                {currency} {minP.toLocaleString()}
                {maxP && maxP !== minP ? <span className="text-gray-400 font-semibold"> – {maxP.toLocaleString()}</span> : ''}
              </span>
            )}
          </div>
        )}

        {/* ── CTA ── */}
        <div className="mt-auto pt-2">
          <div className="flex items-center justify-between bg-gradient-to-r from-primary-500 to-orange-500 rounded-xl px-3 py-2 group-hover:from-primary-600 group-hover:to-orange-600 transition-all duration-200 shadow-sm shadow-primary-200">
            <span className="text-white text-xs font-bold tracking-wide">Buy Now</span>
            <ArrowRight size={13} className="text-white opacity-80 group-hover:translate-x-0.5 transition-transform" />
          </div>
        </div>
      </div>
    </Link>
  )
}
