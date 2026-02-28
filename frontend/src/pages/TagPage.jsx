import { useState } from 'react'
import { useParams, Link } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { Tag, ArrowLeft, ChevronLeft, ChevronRight } from 'lucide-react'
import { getTagProducts } from '../api/tags'
import ProductCard from '../components/ui/ProductCard'
import { SkeletonCard } from '../components/ui/Skeleton'

export default function TagPage() {
  const { slug } = useParams()
  const [page, setPage] = useState(1)

  const { data, isLoading, isError } = useQuery({
    queryKey: ['tag', slug, page],
    queryFn: () => getTagProducts(slug, page),
    keepPreviousData: true,
    staleTime: 60_000,
  })

  const tag = data?.data?.tag
  const products = data?.data?.products?.data ?? []
  const meta = data?.data?.products?.meta

  const tagColor = '#8b5cf6'

  if (isError)
    return (
      <div className="max-w-7xl mx-auto px-4 py-20 text-center">
        <p className="text-slate-500">Failed to load products for this tag.</p>
        <Link to="/" className="text-primary-600 font-semibold mt-4 inline-block">
          ← Back to home
        </Link>
      </div>
    )

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      {/* Back */}
      <Link
        to="/"
        className="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-primary-600 transition-colors mb-6"
      >
        <ArrowLeft size={14} /> Back to home
      </Link>

      {/* Header */}
      <div className="flex items-center gap-4 mb-8">
        <div
          className="w-12 h-12 rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0"
          style={{ backgroundColor: tagColor + '20', border: `1.5px solid ${tagColor}40` }}
        >
          <Tag size={22} style={{ color: tagColor }} />
        </div>
        <div>
          {isLoading ? (
            <div className="space-y-2">
              <div className="skeleton h-7 w-40 rounded-lg" />
              <div className="skeleton h-4 w-24 rounded" />
            </div>
          ) : (
            <>
              <h1 className="text-2xl font-extrabold text-slate-900 tracking-tight">{tag?.name}</h1>
              <p className="text-sm text-slate-400 mt-0.5">{meta?.total ?? 0} products</p>
            </>
          )}
        </div>
      </div>

      {/* Products grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
        {isLoading
          ? Array.from({ length: 6 }).map((_, i) => <SkeletonCard key={i} />)
          : products.map((p, i) => <ProductCard key={p.id} product={p} index={i} />)}
        {!isLoading && products.length === 0 && (
          <p className="col-span-3 text-center text-slate-400 py-16">
            No products found for this tag.
          </p>
        )}
      </div>

      {/* Pagination */}
      {meta && meta.last_page > 1 && (
        <div className="flex items-center justify-center gap-3 mt-10">
          <button
            onClick={() => setPage((p) => Math.max(1, p - 1))}
            disabled={page === 1}
            className="w-9 h-9 rounded-full border border-slate-200 flex items-center justify-center hover:border-primary-300 hover:text-primary-600 disabled:opacity-40 transition-all"
          >
            <ChevronLeft size={16} />
          </button>

          <span className="text-sm text-slate-500 font-medium">
            Page {meta.current_page} of {meta.last_page}
          </span>

          <button
            onClick={() => setPage((p) => Math.min(meta.last_page, p + 1))}
            disabled={page === meta.last_page}
            className="w-9 h-9 rounded-full border border-slate-200 flex items-center justify-center hover:border-primary-300 hover:text-primary-600 disabled:opacity-40 transition-all"
          >
            <ChevronRight size={16} />
          </button>
        </div>
      )}
    </div>
  )
}
