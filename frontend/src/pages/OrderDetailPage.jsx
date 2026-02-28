import { useParams, useSearchParams, Link } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import {
  CheckCircle, Clock, XCircle, Copy, Gift, ArrowLeft,
  Loader2, RefreshCw, CreditCard, ExternalLink, Info,
  Barcode, AlertCircle, Eye, EyeOff,
} from 'lucide-react'
import { useState, useCallback } from 'react'
import { getOrderById, fetchOrderCards } from '../api/orders'

const STATUS_CONFIG = {
  completed: { Icon: CheckCircle, color: 'text-green-500',  bg: 'bg-green-50',  border: 'border-green-200', label: 'Completed'  },
  pending:   { Icon: Clock,       color: 'text-amber-500',  bg: 'bg-amber-50',  border: 'border-amber-200', label: 'Processing' },
  cancelled: { Icon: XCircle,     color: 'text-red-500',    bg: 'bg-red-50',    border: 'border-red-200',   label: 'Cancelled'  },
}

/** Small copy-to-clipboard button */
function CopyBtn({ value }) {
  const [copied, setCopied] = useState(false)
  const handleCopy = () => {
    navigator.clipboard.writeText(String(value)).then(() => {
      setCopied(true)
      setTimeout(() => setCopied(false), 2000)
    }).catch(() => {})
  }
  return (
    <button
      onClick={handleCopy}
      title="Copy"
      className="text-gray-400 hover:text-white transition-colors ml-3 flex-shrink-0"
    >
      {copied
        ? <CheckCircle size={14} className="text-green-400" />
        : <Copy size={14} />}
    </button>
  )
}

/** A single labelled field row with optional hide/show and copy button */
function Field({ label, value, mono = true, secret = false }) {
  const [revealed, setRevealed] = useState(false)
  if (!value) return null
  const displayValue = secret && !revealed ? '•'.repeat(Math.min(String(value).length, 8)) : value
  return (
    <div className="mb-3">
      <p className="text-xs text-gray-400 mb-1">{label}</p>
      <div className="flex items-center justify-between bg-white/10 rounded-xl px-3 py-2.5 gap-2">
        <span className={`text-sm break-all select-${secret && !revealed ? 'none' : 'all'} ${mono ? 'font-mono tracking-wider' : ''}`}>
          {displayValue}
        </span>
        <div className="flex items-center gap-1.5 flex-shrink-0">
          {secret && (
            <button
              onClick={() => setRevealed(r => !r)}
              title={revealed ? 'Hide' : 'Show'}
              className="text-gray-400 hover:text-white transition-colors"
            >
              {revealed
                ? <EyeOff size={14} />
                : <Eye size={14} />}
            </button>
          )}
          <CopyBtn value={value} />
        </div>
      </div>
    </div>
  )
}

/**
 * Displays a single gift card from the Woohoo Activated Cards API response.
 *
 * Handles all card variants:
 *  a) Standard          — cardNumber + cardPin
 *  b) Amazon / CLAIMCODE— cardNumber (Gift Card ID) + cardPin (14-char Gift Card Code)
 *  c) BMS / VOUCHERCODE — cardNumber only (no PIN)
 *  d) Barcode cards     — barcode field + formats array
 *  e) Activation code   — activationCode + activationUrl
 *
 * Uses `labels` from Woohoo for accurate field names, with sensible fallbacks.
 */
function CardDetail({ card }) {
  const cardNumber     = card.cardNumber     || card.card_number     || null
  const cardPin        = card.cardPin        || card.card_pin        || card.pin || null
  const activationCode = card.activationCode || card.activation_code || null
  const activationUrl  = card.activationUrl  || card.activation_url  || null
  const barcode        = card.barcode        || null
  const sequenceNumber = card.sequenceNumber || null

  // Use Woohoo-provided labels; fall back gracefully
  const labelNumber    = card.labels?.cardNumber     || 'Gift Card Number'
  const labelPin       = card.labels?.cardPin        || 'Gift Card PIN'
  const labelActCode   = card.labels?.activationCode || 'Activation Code'
  const labelSeq       = card.labels?.sequenceNumber || 'Sequence Number'
  const labelValidity  = card.labels?.validity       || 'Valid Till'

  const validityRaw = card.validity || card.expiryDate || null
  const validityStr = validityRaw
    ? (() => {
        try {
          return new Date(validityRaw).toLocaleDateString('en-IN', {
            day: 'numeric', month: 'long', year: 'numeric',
          })
        } catch { return validityRaw }
      })()
    : null

  // Special instruction: Woohoo sends it as an object {label, url} or an empty string
  const specialInstr  = card.specialInstruction
  const hasSpecialInstr = specialInstr && (
    (typeof specialInstr === 'object' && specialInstr?.label) ||
    (typeof specialInstr === 'string' && specialInstr.length > 0)
  )

  const balanceInstr = card.balanceEnquiryInstruction || null

  return (
    <div className="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl p-5 text-white shadow-xl">
      {/* Header */}
      <div className="flex items-center justify-between mb-4">
        <div className="flex items-center gap-2">
          <CreditCard size={18} className="text-primary-400" />
          <span className="text-sm font-semibold text-gray-300">
            {card.productName || 'Gift Card'}
          </span>
        </div>
        {card.amount && (
          <span className="text-sm font-bold text-white bg-white/10 rounded-lg px-2.5 py-1">
            ₹ {Number(card.amount).toLocaleString('en-IN')}
          </span>
        )}
      </div>

      {/* Core card credentials */}
      {cardNumber     && <Field label={labelNumber}  value={cardNumber} />}
      {cardPin        && <Field label={labelPin}      value={cardPin}         secret />}
      {activationCode && !cardPin && <Field label={labelActCode} value={activationCode} secret />}
      {sequenceNumber && <Field label={labelSeq}      value={sequenceNumber} />}
      {barcode        && <Field label="Barcode"       value={barcode} />}

      {/* Activation URL */}
      {activationUrl && (
        <div className="mb-3">
          <p className="text-xs text-gray-400 mb-1">Activation Link</p>
          <a
            href={activationUrl}
            target="_blank"
            rel="noopener noreferrer"
            className="inline-flex items-center gap-1.5 text-primary-300 hover:text-primary-200 text-xs underline break-all"
          >
            Activate your card <ExternalLink size={11} />
          </a>
        </div>
      )}

      {/* Validity */}
      {validityStr && (
        <p className="text-xs text-gray-400 mt-2">
          {labelValidity}: <span className="text-gray-200 font-semibold">{validityStr}</span>
        </p>
      )}

      {/* Balance enquiry instruction */}
      {balanceInstr && (
        <div className="mt-3 flex items-start gap-2 bg-white/5 rounded-xl p-3">
          <Info size={13} className="text-blue-400 mt-0.5 flex-shrink-0" />
          <p className="text-xs text-gray-300 leading-relaxed">{balanceInstr}</p>
        </div>
      )}

      {/* Special instruction */}
      {hasSpecialInstr && (
        <div className="mt-3 flex items-start gap-2 bg-amber-500/10 rounded-xl p-3">
          <AlertCircle size={13} className="text-amber-400 mt-0.5 flex-shrink-0" />
          {typeof specialInstr === 'object' ? (
            <p className="text-xs text-gray-300 leading-relaxed">
              {specialInstr.url
                ? <a href={specialInstr.url} target="_blank" rel="noopener noreferrer"
                    className="underline text-amber-300 hover:text-amber-200">
                    {specialInstr.label}
                  </a>
                : specialInstr.label}
            </p>
          ) : (
            <p className="text-xs text-gray-300 leading-relaxed">{specialInstr}</p>
          )}
        </div>
      )}
    </div>
  )
}

/** Delivery summary badge row */
function DeliveryBadge({ delivery }) {
  if (!delivery?.summary) return null
  const s = delivery.summary
  return (
    <div className="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-2">
      {[
        { label: 'Sent',      val: s.sent,       color: 'text-green-600' },
        { label: 'Delivered', val: s.delivered,   color: 'text-emerald-600' },
        { label: 'In Progress', val: s.inProgress, color: 'text-amber-600' },
        { label: 'Failed',    val: s.failed,     color: 'text-red-500' },
      ].map(({ label, val, color }) => (
        <div key={label} className="rounded-xl bg-gray-50 border border-gray-100 p-2.5 text-center">
          <p className={`text-base font-bold ${color}`}>{val ?? 0}</p>
          <p className="text-xs text-gray-400">{label}</p>
        </div>
      ))}
    </div>
  )
}

export default function OrderDetailPage() {
  const { id }    = useParams()
  const [params]  = useSearchParams()
  const payStatus = params.get('payment')

  const { data, isLoading, isError, refetch, isFetching } = useQuery({
    queryKey:  ['order', id],
    queryFn:   () => getOrderById(id),
    staleTime: 10_000,
    refetchInterval: (query) => {
      const order = query?.state?.data?.data
      return order?.status === 'pending' ? 8000 : false
    },
  })

  // Live card data fetched directly from the /cards endpoint
  const [liveCards, setLiveCards]           = useState(null)
  const [liveDelivery, setLiveDelivery]     = useState(null)
  const [liveMode, setLiveMode]             = useState(null)
  const [cardsFetching, setCardsFetching]   = useState(false)
  const [cardsError, setCardsError]         = useState(null)

  const handleFetchCards = useCallback(async () => {
    setCardsFetching(true)
    setCardsError(null)
    try {
      const res = await fetchOrderCards(id)
      const payload = res?.data
      if (payload?.cards?.length) {
        setLiveCards(payload.cards)
        setLiveDelivery(payload.card_delivery ?? null)
        setLiveMode(payload.delivery_mode ?? null)
      } else {
        setCardsError('Card details are not yet available. Please try again in a moment.')
      }
    } catch {
      setCardsError('Could not fetch card details. Please try again.')
    } finally {
      setCardsFetching(false)
    }
  }, [id])

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

  // Prefer live-fetched cards over the TanStack Query cached order data
  const rawCards    = liveCards ?? order.card_details
  const cardArr     = rawCards ? (Array.isArray(rawCards) ? rawCards : [rawCards]) : []
  const deliveryMode = liveMode ?? order.delivery_mode

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
                {new Date(order.created_at).toLocaleDateString('en-IN', {
                  day: 'numeric', month: 'long', year: 'numeric',
                  hour: '2-digit', minute: '2-digit',
                })}
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
            <div className="text-right">
              <p className="font-bold text-gray-900 text-sm">
                {order.currency_code} {Number(order.total_amount)?.toLocaleString()}
              </p>
              {order.points_used > 0 && (
                <p className="text-xs text-amber-600 mt-0.5">
                  -{order.points_used} pts applied
                </p>
              )}
            </div>
          </div>
        </div>
      )}

      {/* Delivery status */}
      <div className="card p-5 mb-4">
        <h3 className="text-sm font-semibold text-gray-700 mb-3">Delivery Status</h3>
        <div className="flex items-center gap-2 text-sm flex-wrap">
          <span className={`w-2.5 h-2.5 rounded-full flex-shrink-0 ${
            order.delivery_status === 'fulfilled' ? 'bg-green-500'
            : order.delivery_status === 'failed' ? 'bg-red-500'
            : 'bg-amber-400'
          }`} />
          <span className="capitalize font-medium text-gray-700">
            {order.delivery_status ?? 'Pending'}
          </span>
          {order.woohoo_refno && (
            <span className="text-gray-400 ml-1 font-mono text-xs">
              Ref: {order.woohoo_refno}
            </span>
          )}
          {deliveryMode && deliveryMode !== 'API' && (
            <span className="ml-auto inline-flex items-center gap-1 text-xs text-gray-400 bg-gray-50 border border-gray-100 rounded-lg px-2 py-0.5">
              <Barcode size={12} /> Delivered via {deliveryMode.toLowerCase()}
            </span>
          )}
        </div>

        {/* Delivery channel summary — only meaningful when Woohoo sends via email/SMS */}
        {deliveryMode && deliveryMode !== 'API' && (liveDelivery ?? order.card_delivery) && (
          <DeliveryBadge delivery={liveDelivery ?? order.card_delivery} />
        )}

        {order.status === 'pending' && (
          <div className="flex items-center gap-2 mt-3">
            <Loader2 size={14} className="text-amber-500 animate-spin" />
            <span className="text-xs text-amber-600">Processing your gift card…</span>
            <button onClick={handleFetchCards} disabled={cardsFetching}
              className="ml-auto text-xs text-primary-500 flex items-center gap-1 hover:text-primary-600">
              <RefreshCw size={12} className={cardsFetching ? 'animate-spin' : ''} /> Refresh
            </button>
          </div>
        )}
        {order.status !== 'pending' && (
          <button onClick={handleFetchCards} disabled={cardsFetching}
            className="mt-3 flex items-center gap-1 text-xs text-gray-400 hover:text-primary-500 transition-colors">
            <RefreshCw size={11} className={cardsFetching ? 'animate-spin' : ''} /> Refresh
          </button>
        )}
      </div>

      {/* EMAIL/SMS delivery notice — card details not returned for these modes */}
      {deliveryMode && deliveryMode !== 'API' && (
        <div className="card p-5 mb-4 flex items-start gap-3">
          <Info size={18} className="text-blue-400 mt-0.5 flex-shrink-0" />
          <div>
            <p className="font-semibold text-gray-700 text-sm">Card delivered via {deliveryMode}</p>
            <p className="text-xs text-gray-500 mt-0.5">
              Your gift card details have been sent directly to your email/phone by Woohoo.
              Please check your inbox or messages.
            </p>
          </div>
        </div>
      )}

      {/* Gift card details */}
      {cardArr.length > 0 && (
        <div className="mb-4">
          <h3 className="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <Gift size={15} className="text-primary-500" />
            Your Gift Card{cardArr.length > 1 ? `s (${cardArr.length})` : ''}
          </h3>
          <div className="space-y-3">
            {cardArr.map((card, i) => (
              <CardDetail key={card.cardId ?? i} card={card} />
            ))}
          </div>
          <p className="text-xs text-gray-400 mt-3 text-center">
            Save these details safely — they will not be resent.
          </p>
        </div>
      )}

      {/* Empty state — completed but no cards (email/SMS delivery, or still processing) */}
      {order.status === 'completed' && cardArr.length === 0 && !deliveryMode && (
        <div className="card p-6 text-center text-gray-500">
          <Gift size={32} className="mx-auto mb-3 text-gray-300" />
          <p className="text-sm font-medium text-gray-700">Card details are being processed.</p>
          <p className="text-xs text-gray-400 mt-1">Click the button below to fetch them now.</p>
          {cardsError && (
            <p className="mt-2 text-xs text-red-500">{cardsError}</p>
          )}
          <button onClick={handleFetchCards} disabled={cardsFetching}
            className="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-primary-500 px-4 py-2 text-xs font-semibold text-white hover:bg-primary-600 disabled:opacity-50 transition-colors">
            <RefreshCw size={12} className={cardsFetching ? 'animate-spin' : ''} /> Refresh Card Details
          </button>
        </div>
      )}

      {/* Loyalty points earned */}
      {order.points_earned > 0 && (
        <div className="card p-4 mb-4 flex items-center gap-3">
          <span className="text-2xl">⭐</span>
          <div>
            <p className="text-sm font-semibold text-amber-700">
              You earned {Number(order.points_earned).toLocaleString()} PayFlex Points!
            </p>
            <p className="text-xs text-gray-500 mt-0.5">
              Points are valid for 30 days and will appear in your balance shortly.
            </p>
          </div>
        </div>
      )}
    </div>
  )
}
