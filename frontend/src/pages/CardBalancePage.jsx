import { useState } from 'react'
import {
  CreditCard,
  Search,
  CheckCircle,
  AlertCircle,
  Loader2,
  Calendar,
  Wallet,
  RefreshCw,
  Info,
  Eye,
  EyeOff,
} from 'lucide-react'
import { checkCardBalance } from '../api/balance'

/* ── helpers ──────────────────────────────────────────────────────────────── */
function formatExpiry(iso) {
  if (!iso) return null
  try {
    return new Date(iso).toLocaleDateString('en-IN', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    })
  } catch {
    return iso
  }
}

function StatCard({ icon: Icon, label, value, highlight = false }) {
  return (
    <div
      className={`rounded-2xl p-5 flex flex-col gap-1.5 border
      ${
        highlight
          ? 'bg-gradient-to-br from-primary-500 to-primary-700 border-primary-400 text-white'
          : 'bg-white border-gray-100 text-gray-800'
      }`}
    >
      <div
        className={`flex items-center gap-2 ${highlight ? 'text-primary-100' : 'text-gray-400'}`}
      >
        <Icon size={14} />
        <span className="text-xs font-medium">{label}</span>
      </div>
      <p
        className={`text-2xl font-extrabold leading-tight ${highlight ? 'text-white' : 'text-gray-900'}`}
      >
        {value}
      </p>
    </div>
  )
}

/* ── main component ───────────────────────────────────────────────────────── */
export default function CardBalancePage() {
  const [cardNumber, setCardNumber] = useState('')
  const [pin, setPin] = useState('')
  const [sku, setSku] = useState('')
  const [showPin, setShowPin] = useState(false)
  const [showAdvanced, setShowAdvanced] = useState(false)

  const [loading, setLoading] = useState(false)
  const [result, setResult] = useState(null) // success data
  const [error, setError] = useState('') // error message

  const handleCheck = async (e) => {
    e.preventDefault()
    if (!cardNumber.trim()) return

    setLoading(true)
    setResult(null)
    setError('')

    try {
      const res = await checkCardBalance({
        cardNumber: cardNumber.trim(),
        pin: pin.trim() || undefined,
        sku: sku.trim() || undefined,
      })
      setResult(res.data)
    } catch (err) {
      setError(
        err.response?.data?.message ||
          'Balance enquiry failed. Please check the card details and try again.'
      )
    } finally {
      setLoading(false)
    }
  }

  const handleReset = () => {
    setResult(null)
    setError('')
    setCardNumber('')
    setPin('')
    setSku('')
  }

  const currencySymbol = result?.currency?.symbol ?? '₹'
  const balance =
    result?.balance != null
      ? `${currencySymbol} ${Number(result.balance).toLocaleString('en-IN', { minimumFractionDigits: 2 })}`
      : '—'

  const cardStatus = result?.status ?? null
  const isActive =
    cardStatus?.toUpperCase() === 'ACTIVATED' || cardStatus?.toUpperCase() === 'ACTIVE'

  return (
    <div className="min-h-[70vh] max-w-lg mx-auto px-4 sm:px-6 py-12">
      {/* Header */}
      <div className="text-center mb-10">
        <div className="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-primary-500/30">
          <Wallet size={28} className="text-white" />
        </div>
        <h1 className="text-3xl font-extrabold text-gray-900">Check Card Balance</h1>
        <p className="text-gray-500 mt-2 text-sm">
          Enter your gift card number to instantly check the remaining balance. No login required.
        </p>
      </div>

      {/* Form */}
      {!result && (
        <form onSubmit={handleCheck} className="card p-6 space-y-4">
          {/* Card Number */}
          <div>
            <label className="block text-sm font-semibold text-gray-700 mb-1.5">
              Gift Card Number <span className="text-red-400">*</span>
            </label>
            <div className="relative">
              <CreditCard
                size={16}
                className="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"
              />
              <input
                type="text"
                value={cardNumber}
                onChange={(e) => setCardNumber(e.target.value)}
                placeholder="e.g. 1234567890123456"
                required
                autoComplete="off"
                className="w-full pl-10 pr-4 py-2.5 border-2 border-gray-200 focus:border-primary-400 rounded-xl text-sm outline-none transition-all font-mono tracking-wider"
              />
            </div>
          </div>

          {/* PIN */}
          <div>
            <label className="block text-sm font-semibold text-gray-700 mb-1.5">
              PIN{' '}
              <span className="text-gray-400 font-normal text-xs">(optional for most cards)</span>
            </label>
            <div className="relative">
              <input
                type={showPin ? 'text' : 'password'}
                value={pin}
                onChange={(e) => setPin(e.target.value)}
                placeholder="e.g. 123456"
                autoComplete="off"
                className="w-full pr-10 px-4 py-2.5 border-2 border-gray-200 focus:border-primary-400 rounded-xl text-sm outline-none transition-all font-mono tracking-widest"
              />
              <button
                type="button"
                onClick={() => setShowPin((v) => !v)}
                className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                tabIndex={-1}
              >
                {showPin ? <EyeOff size={15} /> : <Eye size={15} />}
              </button>
            </div>
          </div>

          {/* Advanced — SKU */}
          <div>
            <button
              type="button"
              onClick={() => setShowAdvanced((v) => !v)}
              className="text-xs text-gray-400 hover:text-gray-600 flex items-center gap-1 transition-colors"
            >
              <Info size={11} />
              {showAdvanced ? 'Hide advanced options' : 'Advanced options (SKU)'}
            </button>
            {showAdvanced && (
              <div className="mt-2">
                <input
                  type="text"
                  value={sku}
                  onChange={(e) => setSku(e.target.value)}
                  placeholder="Product SKU (optional)"
                  className="w-full px-4 py-2.5 border-2 border-gray-200 focus:border-primary-400 rounded-xl text-sm outline-none transition-all font-mono"
                />
                <p className="text-xs text-gray-400 mt-1">
                  Only required for specific card types. Leave blank if unsure.
                </p>
              </div>
            )}
          </div>

          {/* Error */}
          {error && (
            <div className="flex items-start gap-2 bg-red-50 border border-red-200 rounded-xl px-4 py-3">
              <AlertCircle size={15} className="text-red-500 mt-0.5 shrink-0" />
              <p className="text-sm text-red-600">{error}</p>
            </div>
          )}

          {/* Submit */}
          <button
            type="submit"
            disabled={loading || !cardNumber.trim()}
            className="btn-primary w-full !py-3 !rounded-xl disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {loading ? (
              <>
                <Loader2 size={16} className="animate-spin" /> Checking…
              </>
            ) : (
              <>
                <Search size={16} /> Check Balance
              </>
            )}
          </button>

          <p className="text-center text-xs text-gray-400">
            Your card details are never stored or shared.
          </p>
        </form>
      )}

      {/* Result */}
      {result && (
        <div className="space-y-4">
          {/* Status banner */}
          <div
            className={`rounded-2xl p-4 flex items-center gap-3 border ${
              isActive ? 'bg-green-50 border-green-200' : 'bg-amber-50 border-amber-200'
            }`}
          >
            <CheckCircle size={20} className={isActive ? 'text-green-500' : 'text-amber-500'} />
            <div>
              <p
                className={`font-semibold text-sm ${isActive ? 'text-green-800' : 'text-amber-800'}`}
              >
                Card {cardStatus ?? 'Found'}
              </p>
              <p className="text-xs text-gray-500 font-mono mt-0.5">
                {result.card_number || cardNumber}
              </p>
            </div>
          </div>

          {/* Stats */}
          <div className="grid grid-cols-2 gap-3">
            <StatCard icon={Wallet} label="Available Balance" value={balance} highlight />
            <StatCard
              icon={Calendar}
              label="Valid Till"
              value={result.expiry ? formatExpiry(result.expiry) : 'N/A'}
            />
          </div>

          {/* Currency info */}
          {result.currency && (
            <div className="card p-4 flex items-center gap-3">
              <div className="w-9 h-9 rounded-xl bg-primary-50 flex items-center justify-center text-lg font-bold text-primary-600">
                {result.currency.symbol ?? '₹'}
              </div>
              <div>
                <p className="text-xs text-gray-400">Currency</p>
                <p className="text-sm font-semibold text-gray-700">
                  {result.currency.code} ({result.currency.numericCode})
                </p>
              </div>
            </div>
          )}

          {/* Actions */}
          <div className="flex gap-3">
            <button
              onClick={handleReset}
              className="flex-1 inline-flex items-center justify-center gap-1.5 border-2 border-gray-200 hover:border-primary-300 rounded-xl py-2.5 text-sm font-semibold text-gray-700 hover:text-primary-600 transition-all"
            >
              <RefreshCw size={14} /> Check Another Card
            </button>
          </div>

          <p className="text-center text-xs text-gray-400">
            Balance is fetched live. Results are accurate as of now.
          </p>
        </div>
      )}

      {/* Info box */}
      {!result && (
        <div className="mt-6 rounded-2xl bg-blue-50 border border-blue-100 p-4">
          <div className="flex items-start gap-2">
            <Info size={14} className="text-blue-400 mt-0.5 shrink-0" />
            <div className="text-xs text-blue-700 space-y-1">
              <p className="font-semibold">Tips:</p>
              <ul className="list-disc list-inside space-y-0.5 text-blue-600">
                <li>The card number is usually 16–19 digits.</li>
                <li>PIN is required for some cards (e.g., Amazon Pay).</li>
                <li>
                  After a successful resend, the PIN may have been reset — use the new PIN from the
                  resent message.
                </li>
              </ul>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
