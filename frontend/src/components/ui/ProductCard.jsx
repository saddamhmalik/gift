import { useState } from 'react'
import { Link } from 'react-router-dom'
import { Sparkles, TrendingUp } from 'lucide-react'

const THEMES = [
  { img: 'from-slate-600   to-slate-900',   border: 'from-slate-300   via-slate-200   to-purple-300',  shadow: 'rgba(100,116,139,0.25)' },
  { img: 'from-violet-600  to-purple-900',  border: 'from-violet-300  via-purple-200  to-pink-300',    shadow: 'rgba(139,92,246,0.25)'  },
  { img: 'from-blue-600    to-indigo-900',  border: 'from-blue-300    via-indigo-200  to-violet-300',  shadow: 'rgba(99,102,241,0.25)'  },
  { img: 'from-rose-600    to-pink-900',    border: 'from-rose-300    via-pink-200    to-orange-300',  shadow: 'rgba(244,63,94,0.25)'   },
  { img: 'from-emerald-600 to-teal-900',    border: 'from-emerald-300 via-teal-200   to-cyan-300',    shadow: 'rgba(16,185,129,0.25)'  },
  { img: 'from-amber-600   to-orange-900',  border: 'from-amber-300   via-orange-200 to-rose-300',    shadow: 'rgba(245,158,11,0.25)'  },
  { img: 'from-indigo-600  to-blue-900',    border: 'from-indigo-300  via-blue-200   to-sky-300',     shadow: 'rgba(79,70,229,0.25)'   },
  { img: 'from-fuchsia-600 to-violet-900',  border: 'from-fuchsia-300 via-violet-200 to-indigo-300',  shadow: 'rgba(168,85,247,0.25)'  },
]

export default function ProductCard({ product, index = 0 }) {
  const [imgError, setImgError] = useState(false)

  const minP     = parseFloat(product.min_price) || 0
  const maxP     = parseFloat(product.max_price) || 0
  const dealP    = product.is_on_deal && product.deal_price ? parseFloat(product.deal_price) : null
  const currency = product.currency_code ?? 'INR'
  const savePct  = dealP && minP ? Math.round((1 - dealP / minP) * 100) : null

  const imgSrc  = product.image_url || product.thumbnail_url
  const showImg = imgSrc && !imgError

  const theme = THEMES[index % THEMES.length]

  const priceLabel = dealP
    ? `${currency} ${dealP.toLocaleString()}`
    : minP
      ? `${currency} ${minP.toLocaleString()}${maxP && maxP !== minP ? ` – ${maxP.toLocaleString()}` : ''}`
      : null

  const desc = product.offer_short_desc || product.description || ''

  return (
    /* Gradient border wrapper — 1px gradient background, inner card clips to white */
    <div
      className={`group p-[1.5px] rounded-2xl bg-gradient-to-br ${theme.border} shadow-md transition-all duration-300 ease-out hover:-translate-y-1.5`}
      style={{ '--shadow-color': theme.shadow }}
    >
    <Link
      to={`/products/${product.slug || product.id}`}
      className="flex flex-col bg-white rounded-[14px] overflow-hidden h-full"
      style={{
        boxShadow: `0 4px 24px ${theme.shadow}`,
      }}
      onMouseEnter={e => { e.currentTarget.style.boxShadow = `0 16px 48px ${theme.shadow}` }}
      onMouseLeave={e => { e.currentTarget.style.boxShadow = `0 4px 24px ${theme.shadow}` }}
    >
      {/* ── Gift card image ── */}
      <div className="relative overflow-hidden rounded-t-[14px]" style={{ aspectRatio: '16/7' }}>

        {showImg ? (
          <>
            <img
              src={imgSrc}
              alt={product.name}
              onError={() => setImgError(true)}
              className="w-full h-full object-cover group-hover:scale-[1.04] transition-transform duration-500 ease-out"
              loading="lazy"
              decoding="async"
            />
            {/* Subtle top gradient for badge legibility */}
            <div className="absolute inset-0 bg-gradient-to-b from-black/25 via-transparent to-transparent" />
          </>
        ) : (
          <div className={`w-full h-full bg-gradient-to-br ${theme.img} flex items-center justify-center relative`}>
            {/* Decorative circles */}
            <div className="absolute -top-6 -right-6 w-28 h-28 rounded-full bg-white/10" />
            <div className="absolute -bottom-8 -left-8 w-36 h-36 rounded-full bg-white/10" />
            <p className="relative text-white/90 font-extrabold text-base leading-snug text-center px-5 drop-shadow-sm line-clamp-3">
              {product.name}
            </p>
          </div>
        )}

        {/* Discount badge — top-left */}
        {savePct > 0 && (
          <div className="absolute top-3 left-3 flex flex-col items-center bg-emerald-500 text-white rounded-xl px-2.5 py-1.5 shadow-lg shadow-emerald-600/30 leading-none">
            <span className="text-[13px] font-extrabold tracking-tight">{savePct}%</span>
            <span className="text-[7px] font-bold uppercase tracking-widest opacity-90">OFF</span>
          </div>
        )}

        {/* Category chip — top-right */}
        {product.category?.name && (
          <div className="absolute top-3 right-3 bg-black/40 backdrop-blur-md text-white/90 text-[9px] font-semibold uppercase tracking-wider px-2.5 py-1 rounded-full border border-white/15">
            {product.category.name}
          </div>
        )}
      </div>

      {/* ── Content ── */}
      <div className="flex items-center justify-between px-4 py-2.5 gap-3">

        {/* Left: name + desc */}
        <div className="flex-1 min-w-0">
          <h3 className="text-[13px] font-bold text-slate-900 truncate leading-tight group-hover:text-primary-700 transition-colors duration-200">
            {product.name}
          </h3>
          <p className="text-[11px] text-slate-400 truncate mt-0.5">
            {desc || priceLabel || 'Instant delivery · Earn PayFlex Points'}
          </p>
        </div>

        {/* Right: rewards pill */}
        <div className="flex-shrink-0">
          {savePct > 0 ? (
            <span className="inline-flex items-center gap-1 bg-emerald-50 text-emerald-600 border border-emerald-200 text-[10.5px] font-bold px-2.5 py-1 rounded-full">
              <TrendingUp size={9} />
              Save {savePct}%
            </span>
          ) : (
            <span className="inline-flex items-center gap-1 bg-primary-50 text-primary-600 border border-primary-200 text-[10.5px] font-semibold px-2.5 py-1 rounded-full">
              <Sparkles size={9} />
              Earn Points
            </span>
          )}
        </div>

      </div>
    </Link>
    </div>
  )
}
