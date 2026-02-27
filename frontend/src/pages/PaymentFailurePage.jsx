import { useSearchParams, Link } from 'react-router-dom'
import { XCircle, ArrowLeft, RefreshCw } from 'lucide-react'

export default function PaymentFailurePage() {
  const [params] = useSearchParams()
  const reason   = params.get('reason') || 'Payment was not completed.'
  const orderId  = params.get('order_id')

  return (
    <div className="min-h-[70vh] flex items-center justify-center px-4">
      <div className="max-w-md w-full text-center">
        <div className="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
          <XCircle size={40} className="text-red-500" />
        </div>

        <h1 className="text-2xl font-extrabold text-gray-900 mb-2">Payment Failed</h1>
        <p className="text-gray-500 mb-2 text-sm leading-relaxed">
          {decodeURIComponent(reason)}
        </p>
        {orderId && (
          <p className="text-xs text-gray-400 mb-8">Order ID: #{orderId}</p>
        )}

        <div className="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-sm text-amber-800 mb-8 text-left">
          <p className="font-semibold mb-1">What happened?</p>
          <ul className="list-disc pl-4 space-y-1 text-amber-700">
            <li>Your bank may have declined the transaction.</li>
            <li>Your session may have timed out.</li>
            <li>The payment was cancelled.</li>
          </ul>
          <p className="mt-2 text-amber-600">No amount has been charged to your account.</p>
        </div>

        <div className="flex flex-col sm:flex-row gap-3 justify-center">
          <Link to="/" className="btn-secondary gap-2">
            <ArrowLeft size={15} /> Back to Home
          </Link>
          {orderId ? (
            <Link to={`/orders/${orderId}`} className="btn-primary gap-2">
              <RefreshCw size={15} /> View Order &amp; Retry
            </Link>
          ) : (
            <Link to="/" className="btn-primary gap-2">
              <RefreshCw size={15} /> Try Again
            </Link>
          )}
        </div>
      </div>
    </div>
  )
}
