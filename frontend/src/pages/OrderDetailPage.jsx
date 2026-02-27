import { useParams, useSearchParams, Link } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { CheckCircle, Clock, XCircle, Copy, Gift, ArrowLeft, Loader2, RefreshCw, CreditCard } from 'lucide-react'
import { getOrderById } from '../api/orders'

const STATUS_CONFIG = {
  completed: { Icon: CheckCircle, color: 'text-green-500',  bg: 'bg-green-50',  border: 'border-green-200', label: 'Completed'  },
  pending:   { Icon: Clock,       color: 'text-amber-500',  bg: 'bg-amber-50',  border: 'border-amber-200', label: 'Processing' },
  cancelled: { Icon: XCircle,     color: 'text-red-500',    bg: 'bg-red-50',    border: 'border-red-200',   label: 'Cancelled'  },
}

/**
 * Displays a single gift card from the Woohoo `cards` array.
 *
 * Woohoo returns 3 card variants (all use the same fields, labels differ):
 *  a) Standard          — cardNumber + cardPin
 *  b) Amazon / CLAIMCODE— cardNumber (Gift Card ID) + cardPin (14-char Gift Card Code)
 *  c) BMS / VOUCHERCODE — cardNumber only (no PIN)
 *
 * Woohoo always provides a `labels` object that tells us the correct display
 * name for each field (e.g. "Gift Card Number", "Gift Card Code", "Voucher Code").
 * We use those labels directly so the UI adapts to every variant automatically.
 */
function CardDetail({ card }) {
  const copy = (val) => { navigator.clipboard.writeText(String(val)).catch(() => {}) }

  const cardNumber     = card.cardNumber     || card.card_number     || null
  const cardPin        = card.cardPin        || card.card_pin        || card.pin  || null
  const activationCode = card.activationCode || card.activation_code || null
  const activationUrl  = card.activationUrl  || card.activation_url  || null

  // Use labels from the Woohoo response when available, with sensible fallbacks
  const labelNumber  = card.labels?.cardNumber     || 'Gift Card Number'
  const labelPin     = card.labels?.cardPin        || 'Gift Card PIN'
  const labelActCode = card.labels?.activationCode || 'Activation Code'

  // Validity — Woohoo sends ISO 8601; show a readable date
  const validityRaw = card.validity || card.expiryDate || null
  const validityStr = validityRaw
    ? (() => { try { return new Date(validityRaw).toLocaleDateString('en-IN', { day: 'numeric', month: 'long', year: 'numeric' }) } catch { return validityRaw } })()
    : null

  const Field = ({ label, value }) => (
    <div className="mb-3">
      <p className="text-xs text-gray-400 mb-1">{label}</p>
      <div className="flex items-center justify-between bg-white/10 rounded-xl px-3 py-2.5">
        <span className="font-mono text-sm tracking-wider break-all">{value}</span>
        <button
          onClick={() => copy(value)}
          title="Copy"
          className="text-gray-400 hover:text-white transition-colors ml-3 flex-shrink-0"
        >
          <Copy size={14} />
        </button>
      </div>
    </div>
  )

  return (
    <div className="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl p-5 text-white shadow-xl">
      <div className="flex items-center justify-between mb-4">
        <div className="flex items-center gap-2">
          <CreditCard size={18} className="text-primary-400" />
          <span className="text-sm font-semibold text-gray-300">
            {card.productName || 'Gift Card Details'}
          </span>
        </div>
        <Gift size={18} className="text-primary-400" />
      </div>

      {/* Card Number — always shown when present */}
      {cardNumber && <Field label={labelNumber} value={cardNumber} />}

      {/* PIN — hidden for card-number-only variants (e.g. BMS / VOUCHERCODE) */}
      {cardPin && <Field label={labelPin} value={cardPin} />}

      {/* Activation Code — alternative to PIN for some products */}
      {activationCode && !cardPin && <Field label={labelActCode} value={activationCode} />}

      {/* Activation URL — e.g. Woohoo claim link */}
      {activationUrl && (
        <div className="mb-3">
          <p className="text-xs text-gray-400 mb-1">Activation Link</p>
          <a
            href={activationUrl}
            target="_blank"
            rel="noopener noreferrer"
            className="text-primary-300 hover:text-primary-200 text-xs underline break-all"
          >
            {activationUrl}
          </a>
        </div>
      )}

      {card.amount && (
        <p className="text-xs text-gray-400 mt-1">
          Value: <span className="text-gray-200 font-semibold">₹ {Number(card.amount).toLocaleString()}</span>
        </p>
      )}
      {validityStr && (
        <p className="text-xs text-gray-400 mt-1">
          Valid till: <span className="text-gray-300">{validityStr}</span>
        </p>
      )}
    </div>
  )
}

export default function OrderDetailPage() {
  const { id }      = useParams()
  const [params]    = useSearchParams()
  const payStatus   = params.get('payment')

  const { data, isLoading, isError, refetch, isFetching } = useQuery({
    queryKey:  ['order', id],
    queryFn:   () => getOrderById(id),
    staleTime: 10_000,
    refetchInterval: (query) => {
      // Keep polling while order is still pending (TanStack Query v5 callback)
      const order = query?.state?.data?.data
      return order?.status === 'pending' ? 8000 : false
    },
  })

  const order = data?.data

  if (isLoading) return (
    <div className="min-h-[60vh] flex items-center justify-center">
      <Loader2 size={32} className="text-primary-500 animate-spin" />
    </div>
  )

  if (isError || !order) return (
    <div className="min-h-[60vh] flex flex-col items-center justify-center text-center p-8">
      <div className="text-5xl mb-4">😕</div>
      <h2 className="text-xl font-bold text-gray-800 mb-2">Order not found</h2>
      <Link to="/orders" className="btn-primary mt-4">My Orders</Link>
    </div>
  )

  const { Icon, color, bg, border, label } = STATUS_CONFIG[order.status] ?? STATUS_CONFIG.pending
  const cards = order.card_details

  return (
    <div className="max-w-2xl mx-auto px-4 sm:px-6 py-10">
      <Link to="/orders" className="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-primary-500 mb-8 transition-colors">
        <ArrowLeft size={15} /> My Orders
      </Link>

      {/* Payment success banner */}
      {payStatus === 'success' && (
        <div className="bg-green-50 border border-green-200 rounded-2xl p-4 flex items-start gap-3 mb-6">
          <CheckCircle size={20} className="text-green-500 flex-shrink-0 mt-0.5" />
          <div>
            <p className="font-semibold text-green-800">Payment Successful!</p>
            <p className="text-sm text-green-700 mt-0.5">
              {order.status === 'completed'
                ? 'Your gift card has been delivered below.'
                : 'We\'re processing your gift card — this usually takes under a minute.'}
            </p>
          </div>
        </div>
      )}

      {payStatus === 'paid' && (
        <div className="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3 mb-6">
          <Clock size={20} className="text-amber-500 flex-shrink-0 mt-0.5" />
          <div>
            <p className="font-semibold text-amber-800">Payment received — Delivery in progress</p>
            <p className="text-sm text-amber-700 mt-0.5">We received your payment but are still processing your gift card. Please check back shortly.</p>
          </div>
        </div>
      )}

      {/* Order header */}
      <div className="card p-6 mb-4">
        <div className="flex items-start justify-between gap-4 flex-wrap">
          <div>
            <p className="text-xs text-gray-400 mb-1">Order #{order.id}</p>
            <h1 className="text-xl font-bold text-gray-900">
              {order.item?.product?.name ?? 'Gift Card Order'}
            </h1>
            {order.created_at && (
              <p className="text-xs text-gray-400 mt-1">
                {new Date(order.created_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })}
              </p>
            )}
          </div>
          <div className={`flex items-center gap-2 px-3 py-1.5 rounded-xl border text-sm font-semibold ${bg} ${border} ${color}`}>
            <Icon size={15} />
            {label}
          </div>
        </div>
      </div>

      {/* Order item */}
      {order.item && (
        <div className="card p-5 mb-4">
          <h3 className="text-sm font-semibold text-gray-700 mb-3">Order Item</h3>
          <div className="flex items-center gap-4">
            {order.item.product?.thumbnail_url ? (
              <img src={order.item.product.thumbnail_url} alt="" className="w-14 h-14 rounded-xl object-cover border border-gray-100" />
            ) : (
              <div className="w-14 h-14 rounded-xl bg-primary-50 flex items-center justify-center text-2xl">🎁</div>
            )}
            <div className="flex-1">
              <p className="font-semibold text-gray-800 text-sm">{order.item.product?.name}</p>
              <p className="text-xs text-gray-500 mt-0.5">{order.item.product?.offer_short_desc}</p>
              <div className="flex items-center gap-3 mt-1.5 text-xs text-gray-500">
                <span>Qty: {order.item.quantity}</span>
                <span>·</span>
                <span>{order.currency_code} {Number(order.item.unit_price)?.toLocaleString()} each</span>
              </div>
            </div>
            <p className="font-bold text-gray-900 text-sm">{order.currency_code} {Number(order.total_amount)?.toLocaleString()}</p>
          </div>
        </div>
      )}

      {/* Delivery status */}
      <div className="card p-5 mb-4">
        <h3 className="text-sm font-semibold text-gray-700 mb-3">Delivery Status</h3>
        <div className="flex items-center gap-2 text-sm">
          <span className={`w-2.5 h-2.5 rounded-full ${order.delivery_status === 'fulfilled' ? 'bg-green-500' : order.delivery_status === 'failed' ? 'bg-red-500' : 'bg-amber-400'}`} />
          <span className="capitalize font-medium text-gray-700">{order.delivery_status ?? 'Pending'}</span>
          {order.woohoo_refno && <span className="text-gray-400 ml-2 font-mono text-xs">Ref: {order.woohoo_refno}</span>}
        </div>

        {order.status === 'pending' && (
          <div className="flex items-center gap-2 mt-3">
            <Loader2 size={14} className="text-amber-500 animate-spin" />
            <span className="text-xs text-amber-600">Processing your gift card…</span>
            <button onClick={() => refetch()} disabled={isFetching} className="ml-auto text-xs text-primary-500 flex items-center gap-1 hover:text-primary-600">
              <RefreshCw size={12} className={isFetching ? 'animate-spin' : ''} /> Refresh
            </button>
          </div>
        )}
      </div>

      {/* Card details */}
      {cards && (Array.isArray(cards) ? cards : [cards]).length > 0 && (
        <div className="mb-4">
          <h3 className="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <Gift size={15} className="text-primary-500" /> Your Gift Card(s)
          </h3>
          <div className="space-y-3">
            {(Array.isArray(cards) ? cards : [cards]).map((card, i) => (
              <CardDetail key={i} card={card} />
            ))}
          </div>
          <p className="text-xs text-gray-400 mt-3 text-center">
            Save these details — they will not be shown again if you lose them.
          </p>
        </div>
      )}

      {/* Empty state — pending delivery */}
      {order.status === 'completed' && !cards && (
        <div className="card p-6 text-center text-gray-500">
          <Gift size={32} className="mx-auto mb-3 text-gray-300" />
          <p className="text-sm">Card details are being processed. Please refresh in a moment.</p>
        </div>
      )}
    </div>
  )
}
