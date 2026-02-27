import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { CheckCircle, Clock, XCircle, ChevronRight, Gift, Loader2, ShoppingBag } from 'lucide-react'
import { getOrders } from '../api/orders'

const STATUS_CONFIG = {
  completed: { Icon: CheckCircle, color: 'text-green-500', badge: 'bg-green-100 text-green-700', label: 'Completed'  },
  pending:   { Icon: Clock,       color: 'text-amber-500', badge: 'bg-amber-100 text-amber-700', label: 'Processing' },
  cancelled: { Icon: XCircle,     color: 'text-red-500',   badge: 'bg-red-100 text-red-700',     label: 'Cancelled'  },
}

export default function OrdersPage() {
  const [page, setPage] = useState(1)

  const { data, isLoading, isError } = useQuery({
    queryKey:  ['orders', page],
    queryFn:   () => getOrders(page),
    staleTime: 30_000,
    placeholderData: (prev) => prev,
  })

  // API: { success, data: { data: [...], meta: {...} } }
  const orders = data?.data?.data ?? []
  const meta   = data?.data?.meta ?? {}

  if (isLoading) return (
    <div className="min-h-[60vh] flex items-center justify-center">
      <Loader2 size={30} className="text-primary-500 animate-spin" />
    </div>
  )

  if (isError) return (
    <div className="min-h-[60vh] flex flex-col items-center justify-center text-center p-8">
      <p className="text-gray-500">Failed to load orders.</p>
    </div>
  )

  return (
    <div className="max-w-3xl mx-auto px-4 sm:px-6 py-10">
      <div className="mb-8">
        <h1 className="text-3xl font-extrabold text-gray-900 flex items-center gap-2">
          <ShoppingBag size={28} className="text-primary-500" /> My Orders
        </h1>
        <p className="text-gray-500 mt-1 text-sm">All your gift card orders</p>
      </div>

      {orders.length === 0 ? (
        <div className="text-center py-20">
          <Gift size={56} className="mx-auto text-gray-200 mb-4" />
          <h2 className="text-lg font-semibold text-gray-600 mb-2">No orders yet</h2>
          <p className="text-gray-400 text-sm mb-6">Start gifting — browse our gift card collection.</p>
          <Link to="/" className="btn-primary">Browse Gift Cards</Link>
        </div>
      ) : (
        <div className="space-y-3">
          {orders.map(order => {
            const { Icon, badge, label } = STATUS_CONFIG[order.status] ?? STATUS_CONFIG.pending
            return (
              <Link key={order.id} to={`/orders/${order.id}`} className="card p-5 flex items-center gap-4 hover:shadow-md transition-all">
                {/* Product thumbnail */}
                <div className="flex-shrink-0">
                  {order.item?.product?.thumbnail_url ? (
                    <img src={order.item.product.thumbnail_url} alt="" className="w-14 h-14 rounded-xl object-cover border border-gray-100" />
                  ) : (
                    <div className="w-14 h-14 rounded-xl bg-primary-50 flex items-center justify-center text-2xl">🎁</div>
                  )}
                </div>

                {/* Info */}
                <div className="flex-1 min-w-0">
                  <p className="font-semibold text-gray-800 text-sm truncate">
                    {order.item?.product?.name ?? 'Gift Card'}
                  </p>
                  <div className="flex items-center gap-2 mt-1 flex-wrap">
                    <span className={`badge ${badge}`}>
                      <Icon size={10} className="mr-1" />{label}
                    </span>
                    {order.delivery_status && (
                      <span className="text-xs text-gray-400 capitalize">· {order.delivery_status}</span>
                    )}
                  </div>
                  {order.created_at && (
                    <p className="text-xs text-gray-400 mt-1">
                      {new Date(order.created_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' })}
                    </p>
                  )}
                </div>

                {/* Amount + arrow */}
                <div className="text-right flex-shrink-0">
                  <p className="font-bold text-gray-900 text-sm">
                    {order.currency_code} {Number(order.total_amount)?.toLocaleString()}
                  </p>
                  <ChevronRight size={16} className="text-gray-300 mt-1 ml-auto" />
                </div>
              </Link>
            )
          })}
        </div>
      )}

      {/* Pagination */}
      {(meta?.last_page ?? 1) > 1 && (
        <div className="flex justify-center items-center gap-3 mt-8">
          <button
            onClick={() => setPage(p => Math.max(1, p - 1))}
            disabled={page === 1}
            className="btn-secondary !px-4 !py-2 !text-xs disabled:opacity-40"
          >
            Previous
          </button>
          <span className="text-sm text-gray-500">{page} / {meta?.last_page}</span>
          <button
            onClick={() => setPage(p => Math.min(meta?.last_page ?? p, p + 1))}
            disabled={page === meta?.last_page}
            className="btn-secondary !px-4 !py-2 !text-xs disabled:opacity-40"
          >
            Next
          </button>
        </div>
      )}
    </div>
  )
}
