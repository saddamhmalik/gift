import { useState } from 'react'
import { useParams, Link, useNavigate } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { ShoppingCart, ChevronRight, Shield, Zap, RefreshCw, Info, Loader2, AlertCircle, Star, Gift } from 'lucide-react'
import { getProduct } from '../api/products'
import { getLoyaltyBalance } from '../api/loyalty'
import { useAuth } from '../contexts/AuthContext'
import { useOrder } from '../contexts/OrderContext'
import { initiatePayment, redirectToPayU } from '../api/payment'

/* ─── HTML helpers ──────────────────────────────────────────────────────────── */
function HtmlContent({ html }) {
  if (!html) return null
  const isHtml = /<[a-z][\s\S]*>/i.test(html)
  if (isHtml) return <div className="html-content" dangerouslySetInnerHTML={{ __html: html }} />
  return <p className="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{html}</p>
}

function TncAccordion({ html }) {
  const [open, setOpen] = useState(false)
  const isHtml = /<[a-z][\s\S]*>/i.test(html)
  return (
    <div className="border border-gray-100 rounded-xl overflow-hidden">
      <button
        onClick={() => setOpen(v => !v)}
        className="w-full px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 flex justify-between items-center transition-colors"
      >
        <span className="flex items-center gap-1.5">
          <ChevronRight size={14} className={`text-gray-400 transition-transform duration-200 ${open ? 'rotate-90' : ''}`} />
          Terms &amp; Conditions
        </span>
        <span className="text-xs text-gray-400">{open ? 'Hide' : 'Show'}</span>
      </button>
      {open && (
        <div className="px-4 pb-5 pt-1 border-t border-gray-100 bg-gray-50/50">
          {isHtml
            ? <div className="html-content" dangerouslySetInnerHTML={{ __html: html }} />
            : <p className="text-xs text-gray-500 leading-relaxed whitespace-pre-line">{html}</p>
          }
        </div>
      )}
    </div>
  )
}

/* ─── Loyalty Points Banner ─────────────────────────────────────────────────── */
function LoyaltyEarnBadge({ points }) {
  if (!points || points <= 0) return null
  return (
    <div className="flex items-center gap-2 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2">
      <Star size={14} className="text-amber-500" fill="currentColor" />
      <span className="text-xs font-semibold text-amber-700">
        Earn <span className="font-extrabold">{points.toFixed(0)} PayFlex Points</span> (worth ₹{points.toFixed(0)}) on this purchase
      </span>
    </div>
  )
}

/* ─── Main component ────────────────────────────────────────────────────────── */
export default function ProductDetailPage() {
  const { slug } = useParams()
  const { user } = useAuth()
  const { addItem, loading: orderLoading } = useOrder()
  const navigate = useNavigate()

  const [selectedDenom, setSelectedDenom] = useState(null)
  const [qty, setQty]                     = useState(1)
  const [customPrice, setCustomPrice]     = useState('')
  const [usePoints, setUsePoints]         = useState(false)
  const [buyError, setBuyError]           = useState('')
  const [paying, setPaying]               = useState(false)

  const { data, isLoading, isError } = useQuery({
    queryKey:  ['product', slug],
    queryFn:   () => getProduct(slug),
    staleTime: 1000 * 60 * 5,
  })

  const { data: loyaltyData } = useQuery({
    queryKey:  ['loyalty', 'balance'],
    queryFn:   () => getLoyaltyBalance(),
    enabled:   !!user,
    staleTime: 1000 * 60,
  })

  const product      = data?.data
  const loyaltyInfo  = loyaltyData?.data
  const pointBalance = loyaltyInfo?.balance ?? 0
  const defaultRate  = loyaltyInfo?.default_rate ?? 0.01

  if (isLoading) return (
    <div className="min-h-[60vh] flex items-center justify-center">
      <Loader2 size={32} className="text-primary-500 animate-spin" />
    </div>
  )

  if (isError || !product) return (
    <div className="min-h-[60vh] flex flex-col items-center justify-center text-center p-8">
      <div className="text-6xl mb-4">😕</div>
      <h2 className="text-xl font-bold text-gray-800 mb-2">Product not found</h2>
      <p className="text-gray-500 mb-6">We couldn&apos;t find this gift card.</p>
      <Link to="/" className="btn-primary">Back to Home</Link>
    </div>
  )

  const priceType   = product.price_type ?? 'RANGE'
  const denoms      = Array.isArray(product.denominations) ? product.denominations : []
  const minPrice    = parseFloat(product.min_price) || 0
  const maxPrice    = parseFloat(product.max_price) || 0
  const hasDenoms   = denoms.length > 0
  const isFreeRange = priceType === 'RANGE' && !hasDenoms

  const effectivePrice = hasDenoms
    ? (selectedDenom != null ? parseFloat(selectedDenom) : parseFloat(denoms[0]) || minPrice)
    : (parseFloat(customPrice) || minPrice)

  const orderTotal    = (effectivePrice || 0) * qty
  // Loyalty
  const earnRate      = product.loyalty_rate ?? defaultRate
  const pointsToEarn  = Math.round(orderTotal * earnRate * (usePoints ? Math.max(0, 1 - Math.min(pointBalance, orderTotal) / orderTotal) : 1))
  const pointsApplied = usePoints ? Math.min(pointBalance, orderTotal) : 0
  const amountToPay   = Math.max(1, orderTotal - pointsApplied)

  const handleBuyNow = async () => {
    if (!user) { navigate('/login', { state: { from: { pathname: `/products/${slug}` } } }); return }
    setBuyError('')

    if (hasDenoms && selectedDenom == null) {
      setBuyError('Please select a denomination.')
      return
    }
    if (isFreeRange) {
      const v = parseFloat(customPrice)
      if (!v || v < minPrice || v > maxPrice) {
        setBuyError(`Please enter an amount between ${product.currency_code} ${minPrice.toLocaleString()} – ${maxPrice.toLocaleString()}`)
        return
      }
    }

    setPaying(true)
    try {
      const orderData = await addItem({
        productId:            product.id,
        quantity:             qty,
        unitPrice:            effectivePrice,
        selectedDenomination: String(effectivePrice),
      })

      const res = await initiatePayment({
        order_token:   orderData.order_token,
        points_to_use: pointsApplied,
      })
      const { payu_params } = res.data

      redirectToPayU(payu_params)
    } catch (err) {
      setBuyError(err.response?.data?.message || err.message || 'Something went wrong. Please try again.')
      setPaying(false)
    }
  }

  const busy = orderLoading || paying

  return (
    <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      {/* Breadcrumb */}
      <nav className="flex items-center gap-1.5 text-xs text-gray-400 mb-8 flex-wrap">
        <Link to="/" className="hover:text-primary-500">Home</Link>
        <ChevronRight size={12} />
        {product.category && (
          <>
            <Link to={`/categories/${product.category.slug}`} className="hover:text-primary-500">{product.category.name}</Link>
            <ChevronRight size={12} />
          </>
        )}
        <span className="text-gray-600 font-medium truncate max-w-[200px]">{product.name}</span>
      </nav>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14">
        {/* ── Left: Image ── */}
        <div>
          <div className="aspect-video rounded-3xl overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200 shadow-lg">
            {product.image_url || product.thumbnail_url ? (
              <img src={product.image_url || product.thumbnail_url} alt={product.name} className="w-full h-full object-cover" />
            ) : (
              <div className="w-full h-full flex items-center justify-center text-7xl">🎁</div>
            )}
          </div>

          <div className="grid grid-cols-3 gap-3 mt-5">
            {[
              { Icon: Zap,       label: 'Instant Delivery' },
              { Icon: Shield,    label: 'Secure Payment'   },
              { Icon: RefreshCw, label: '100% Valid'        },
            ].map(({ Icon, label }) => (
              <div key={label} className="bg-gray-50 rounded-xl p-3 text-center">
                <Icon size={18} className="text-primary-500 mx-auto mb-1" />
                <span className="text-xs text-gray-600 font-medium">{label}</span>
              </div>
            ))}
          </div>

          {/* Points earn preview */}
          {orderTotal > 0 && (
            <div className="mt-4">
              <LoyaltyEarnBadge points={Math.round(orderTotal * earnRate)} />
            </div>
          )}
        </div>

        {/* ── Right: Info + actions ── */}
        <div className="flex flex-col gap-5">
          {product.category && (
            <Link to={`/categories/${product.category.slug}`} className="text-xs font-semibold uppercase tracking-widest text-primary-500">
              {product.category.name}
            </Link>
          )}

          <h1 className="text-3xl font-extrabold text-gray-900 leading-tight">{product.name}</h1>

          {product.offer_short_desc && (
            <p className="text-gray-500 text-sm leading-relaxed">{product.offer_short_desc}</p>
          )}

          {/* Price */}
          <div className="flex items-baseline gap-2 flex-wrap">
            {product.is_on_deal && product.deal_price ? (
              <>
                <span className="text-3xl font-extrabold text-red-500">{product.currency_code} {product.deal_price.toLocaleString()}</span>
                <span className="text-lg text-gray-400 line-through">{product.currency_code} {product.min_price?.toLocaleString()}</span>
                <span className="badge bg-red-100 text-red-600">Sale</span>
              </>
            ) : isFreeRange ? (
              <span className="text-sm text-gray-500 font-medium">Enter your desired amount below</span>
            ) : (
              <span className="text-3xl font-extrabold text-gray-900">
                {product.currency_code} {minPrice.toLocaleString()}
                {maxPrice && maxPrice !== minPrice ? ` – ${maxPrice.toLocaleString()}` : ''}
              </span>
            )}
          </div>

          {/* Denomination buttons */}
          {hasDenoms && (
            <div>
              <p className="text-sm font-semibold text-gray-700 mb-2.5">Select Amount</p>
              <div className="flex flex-wrap gap-2">
                {denoms.map((d) => {
                  const val = typeof d === 'object' ? String(d.price ?? d) : String(d)
                  const isSelected = selectedDenom === val || (selectedDenom == null && val === String(denoms[0]))
                  return (
                    <button
                      key={val}
                      onClick={() => setSelectedDenom(val)}
                      className={`px-4 py-2 rounded-xl border-2 text-sm font-semibold transition-all
                        ${isSelected
                          ? 'border-primary-500 bg-primary-50 text-primary-700'
                          : 'border-gray-200 bg-white text-gray-700 hover:border-primary-300'}`}
                    >
                      {product.currency_code} {Number(val)?.toLocaleString()}
                    </button>
                  )
                })}
              </div>
              {priceType === 'RANGE' && (
                <p className="text-xs text-gray-400 mt-1.5">Select one of the available denominations above.</p>
              )}
            </div>
          )}

          {/* Free-form amount */}
          {isFreeRange && (
            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-2">
                Enter Amount
                <span className="text-gray-400 font-normal ml-1">
                  ({product.currency_code} {minPrice.toLocaleString()} – {maxPrice.toLocaleString()})
                </span>
              </label>
              <div className="flex items-center gap-2">
                <span className="text-gray-500 font-medium text-sm">{product.currency_code}</span>
                <input
                  type="number" min={minPrice} max={maxPrice} step="1"
                  value={customPrice} onChange={e => setCustomPrice(e.target.value)}
                  placeholder={String(minPrice)}
                  className="w-40 border-2 border-gray-200 focus:border-primary-400 rounded-xl px-3 py-2 text-sm outline-none transition-all"
                />
              </div>
            </div>
          )}

          {/* Qty */}
          <div className="flex items-center gap-3">
            <span className="text-sm font-semibold text-gray-700">Quantity</span>
            <div className="flex items-center border-2 border-gray-200 rounded-xl overflow-hidden">
              <button onClick={() => setQty(q => Math.max(1, q - 1))} className="px-3 py-2 text-gray-600 hover:bg-gray-50 font-bold transition-colors text-lg leading-none">−</button>
              <span className="px-4 py-2 text-sm font-semibold text-gray-800 border-x-2 border-gray-200 min-w-[2.5rem] text-center">{qty}</span>
              <button onClick={() => setQty(q => Math.min(4, q + 1))} className="px-3 py-2 text-gray-600 hover:bg-gray-50 font-bold transition-colors text-lg leading-none">+</button>
            </div>
            <span className="text-xs text-gray-400">(max 4 per order)</span>
          </div>

          {/* ── Loyalty Points Toggle (logged in users only) ── */}
          {user && pointBalance > 0 && orderTotal > 0 && (
            <div className={`rounded-2xl border-2 p-4 transition-all ${usePoints ? 'border-amber-400 bg-amber-50' : 'border-gray-200 bg-gray-50'}`}>
              <div className="flex items-center justify-between gap-3">
                <div className="flex items-center gap-2.5">
                  <div className="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                    <Star size={16} className="text-amber-500" fill="currentColor" />
                  </div>
                  <div>
                    <p className="text-sm font-semibold text-gray-800">Use PayFlex Points</p>
                    <p className="text-xs text-gray-500">
                      You have <span className="font-bold text-amber-600">{pointBalance.toFixed(0)} pts</span> (worth ₹{pointBalance.toFixed(0)})
                    </p>
                  </div>
                </div>
                <button
                  onClick={() => setUsePoints(v => !v)}
                  className={`relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus:outline-none ${usePoints ? 'bg-amber-500' : 'bg-gray-300'}`}
                >
                  <span className={`inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform ${usePoints ? 'translate-x-6' : 'translate-x-1'}`} />
                </button>
              </div>
              {usePoints && (
                <div className="mt-3 pt-3 border-t border-amber-200">
                  <div className="flex justify-between text-xs text-gray-600">
                    <span>Points applied:</span>
                    <span className="font-semibold text-amber-700">−₹{pointsApplied.toFixed(0)} ({pointsApplied.toFixed(0)} pts)</span>
                  </div>
                  {pointsToEarn > 0 && (
                    <p className="text-xs text-gray-400 mt-1">You'll still earn {pointsToEarn} pts on the paid portion.</p>
                  )}
                </div>
              )}
            </div>
          )}

          {/* Order total */}
          <div className="bg-gradient-to-r from-primary-50 to-amber-50 rounded-2xl p-4 border border-primary-100">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-xs text-gray-500 mb-0.5">
                  {usePoints && pointsApplied > 0 ? 'Amount to Pay' : 'Order Total'}
                </p>
                <p className="text-2xl font-extrabold text-primary-600">
                  {product.currency_code} {amountToPay.toLocaleString()}
                </p>
                {usePoints && pointsApplied > 0 && (
                  <p className="text-xs text-gray-400 mt-0.5">
                    <span className="line-through">₹{orderTotal.toLocaleString()}</span>
                    <span className="text-amber-600 ml-1 font-medium">− {pointsApplied.toFixed(0)} pts</span>
                  </p>
                )}
              </div>
              <div className="text-right text-xs text-gray-500">
                <p>{qty} × {product.currency_code} {Number(effectivePrice)?.toLocaleString()}</p>
                {pointsToEarn > 0 && (
                  <p className="text-amber-600 font-medium mt-1 flex items-center gap-1 justify-end">
                    <Star size={10} fill="currentColor" /> +{pointsToEarn} pts earned
                  </p>
                )}
                <p className="text-green-600 font-medium mt-1 flex items-center gap-1 justify-end">
                  <Shield size={11} /> Secure Checkout
                </p>
              </div>
            </div>
          </div>

          {/* Points promo (not logged in) */}
          {!user && orderTotal > 0 && (
            <div className="flex items-center gap-2 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2">
              <Gift size={14} className="text-amber-500" />
              <span className="text-xs text-amber-700">
                <Link to="/login" className="font-bold underline">Login</Link> to earn <span className="font-bold">{Math.round(orderTotal * earnRate)} PayFlex Points</span> on this purchase!
              </span>
            </div>
          )}

          {/* Error */}
          {buyError && (
            <div className="flex items-start gap-2 bg-red-50 border border-red-200 text-red-600 text-sm px-4 py-3 rounded-xl">
              <AlertCircle size={15} className="flex-shrink-0 mt-0.5" />
              <span>{buyError}</span>
            </div>
          )}

          {/* Buy Now / PayU */}
          {user ? (
            <button
              onClick={handleBuyNow}
              disabled={busy}
              className="btn-primary w-full !py-4 !text-base !rounded-2xl disabled:opacity-60 disabled:cursor-not-allowed"
            >
              {busy ? (
                <><Loader2 size={18} className="animate-spin" /> {paying ? 'Redirecting to PayU…' : 'Preparing order…'}</>
              ) : (
                <><ShoppingCart size={18} /> {usePoints && pointsApplied > 0 ? `Pay ₹${amountToPay.toLocaleString()} via PayU` : 'Pay with PayU'}</>
              )}
            </button>
          ) : (
            <Link
              to="/login"
              state={{ from: { pathname: `/products/${slug}` } }}
              className="btn-primary w-full !py-4 !text-base !rounded-2xl justify-center"
            >
              <ShoppingCart size={18} /> Login to Buy
            </Link>
          )}

          <p className="text-center text-xs text-gray-400">
            Powered by PayU · 256-bit SSL encryption · Instant delivery after payment
          </p>

          {/* Description */}
          {(product.description || product.purchaser_description) && (
            <div className="border-t border-gray-100 pt-5">
              <h3 className="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-1.5">
                <Info size={14} /> About this Gift Card
              </h3>
              <HtmlContent html={product.purchaser_description || product.description} />
            </div>
          )}

          {/* T&C */}
          {product.tnc_content && <TncAccordion html={product.tnc_content} />}
        </div>
      </div>
    </div>
  )
}
