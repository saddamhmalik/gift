import { useState, useEffect, useCallback } from 'react'
import { useSearchParams, Link } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { SlidersHorizontal, X, ChevronLeft, ChevronRight, PackageSearch, CreditCard } from 'lucide-react'
import { searchProducts } from '../api/products'
import ProductCard, { PRODUCT_GRID_CLASS_WITH_SIDEBAR } from '../components/ui/ProductCard'

function SkeletonCard() {
  return (
    <div className="animate-pulse rounded-2xl overflow-hidden bg-slate-100" style={{ height: 220 }}>
      <div className="h-[130px] bg-slate-200" />
      <div className="p-3 space-y-2">
        <div className="h-3 bg-slate-200 rounded w-3/4" />
        <div className="h-3 bg-slate-200 rounded w-1/2" />
      </div>
    </div>
  )
}

const SORT_OPTIONS = [
  { value: 'popular', label: 'Most popular' },
  { value: 'newest', label: 'Newest first' },
  { value: 'price_asc', label: 'Price: low → high' },
  { value: 'price_desc', label: 'Price: high → low' },
]

export default function GiftCardsPage() {
  const [searchParams, setSearchParams] = useSearchParams()
  const [minPrice, setMinPrice] = useState(searchParams.get('min_price') ?? '')
  const [maxPrice, setMaxPrice] = useState(searchParams.get('max_price') ?? '')
  const [sort, setSort] = useState(searchParams.get('sort') ?? 'popular')
  const [filtersOpen, setFiltersOpen] = useState(false)

  useEffect(() => {
    setMinPrice(searchParams.get('min_price') ?? '')
    setMaxPrice(searchParams.get('max_price') ?? '')
    setSort(searchParams.get('sort') ?? 'popular')
  }, [searchParams])

  const pushParams = useCallback(
    (overrides = {}) => {
      setSearchParams({
        ...(minPrice ? { min_price: minPrice } : {}),
        ...(maxPrice ? { max_price: maxPrice } : {}),
        ...(sort !== 'popular' ? { sort } : {}),
        ...overrides,
      })
    },
    [minPrice, maxPrice, sort, setSearchParams]
  )

  const { data, isLoading, isFetching } = useQuery({
    queryKey: [
      'gift-cards-browse',
      searchParams.get('min_price'),
      searchParams.get('max_price'),
      searchParams.get('sort'),
      searchParams.get('page'),
    ],
    queryFn: () =>
      searchProducts({
        min_price: searchParams.get('min_price') ?? undefined,
        max_price: searchParams.get('max_price') ?? undefined,
        sort: searchParams.get('sort') ?? undefined,
        page: Number(searchParams.get('page') ?? 1),
        per_page: 24,
      }),
    staleTime: 30_000,
    placeholderData: (prev) => prev,
  })

  const results = data?.data?.data ?? []
  const meta = data?.data?.meta ?? {}
  const totalHits = meta.total ?? 0
  const lastPage = meta.last_page ?? 1

  const activeFilters = [
    minPrice && { key: 'min_price', label: `Min ₹${minPrice}` },
    maxPrice && { key: 'max_price', label: `Max ₹${maxPrice}` },
  ].filter(Boolean)

  const clearFilter = (key) => {
    const p = Object.fromEntries(searchParams.entries())
    delete p[key]
    p.page = '1'
    if (key === 'min_price') setMinPrice('')
    if (key === 'max_price') setMaxPrice('')
    setSearchParams(p)
  }

  const busy = isLoading || isFetching

  return (
    <div className="min-h-screen bg-gray-50/50">
      <div className="bg-gradient-to-br from-surface-950 via-slate-900 to-surface-950 pt-24 pb-10 px-4">
        <div className="max-w-2xl mx-auto text-center mb-2">
          <div className="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-primary-500/20 mb-4">
            <CreditCard className="text-primary-400" size={24} />
          </div>
          <h1 className="text-2xl sm:text-3xl font-extrabold text-white mb-1">All gift cards</h1>
          {!isLoading && (
            <p className="text-sm text-slate-400 mt-1">
              {totalHits.toLocaleString()} card{totalHits !== 1 ? 's' : ''} available
            </p>
          )}
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="flex flex-col lg:flex-row gap-6">
          <aside className="hidden lg:block w-56 shrink-0">
            <FilterSidebar
              minPrice={minPrice}
              setMinPrice={setMinPrice}
              maxPrice={maxPrice}
              setMaxPrice={setMaxPrice}
              onApply={() => pushParams({ page: '1' })}
              onClear={() => {
                setMinPrice('')
                setMaxPrice('')
                setSearchParams({})
              }}
            />
          </aside>

          <div className="flex-1 min-w-0">
            <div className="flex flex-wrap items-center justify-between gap-3 mb-5">
              <div className="flex items-center gap-2 flex-wrap">
                <button
                  type="button"
                  onClick={() => setFiltersOpen((v) => !v)}
                  className="lg:hidden flex items-center gap-1.5 px-3 py-2 rounded-xl border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm"
                >
                  <SlidersHorizontal size={14} />
                  Filters
                  {activeFilters.length > 0 && (
                    <span className="ml-0.5 w-4 h-4 rounded-full bg-primary-500 text-white text-[10px] font-bold flex items-center justify-center">
                      {activeFilters.length}
                    </span>
                  )}
                </button>

                {activeFilters.map((f) => (
                  <span
                    key={f.key}
                    className="inline-flex items-center gap-1 bg-primary-50 border border-primary-200 text-primary-700 text-xs font-semibold px-2.5 py-1 rounded-full"
                  >
                    {f.label}
                    <button type="button" onClick={() => clearFilter(f.key)} className="hover:text-primary-900">
                      <X size={11} />
                    </button>
                  </span>
                ))}
              </div>

              <select
                value={sort}
                onChange={(e) => {
                  const v = e.target.value
                  setSort(v)
                  setSearchParams({
                    ...(minPrice ? { min_price: minPrice } : {}),
                    ...(maxPrice ? { max_price: maxPrice } : {}),
                    ...(v !== 'popular' ? { sort: v } : {}),
                    page: '1',
                  })
                }}
                className="text-sm border border-gray-200 rounded-xl px-3 py-2 bg-white text-gray-700 outline-none focus:border-primary-400 shadow-sm cursor-pointer"
              >
                {SORT_OPTIONS.map((o) => (
                  <option key={o.value} value={o.value}>
                    {o.label}
                  </option>
                ))}
              </select>
            </div>

            {filtersOpen && (
              <div className="lg:hidden mb-5 bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <FilterSidebar
                  minPrice={minPrice}
                  setMinPrice={setMinPrice}
                  maxPrice={maxPrice}
                  setMaxPrice={setMaxPrice}
                  onApply={() => {
                    pushParams({})
                    setFiltersOpen(false)
                  }}
                  onClear={() => {
                    setMinPrice('')
                    setMaxPrice('')
                    setSearchParams({})
                    setFiltersOpen(false)
                  }}
                />
              </div>
            )}

            {isLoading ? (
              <div className={PRODUCT_GRID_CLASS_WITH_SIDEBAR}>
                {Array.from({ length: 9 }).map((_, i) => (
                  <SkeletonCard key={i} />
                ))}
              </div>
            ) : results.length === 0 ? (
              <div className="flex flex-col items-center justify-center py-20 text-center">
                <div className="w-20 h-20 rounded-3xl bg-gray-100 flex items-center justify-center mb-5">
                  <PackageSearch size={36} className="text-gray-300" />
                </div>
                <h3 className="text-lg font-bold text-gray-800 mb-2">No gift cards found</h3>
                <p className="text-sm text-gray-400 max-w-xs mb-6">Adjust filters or check back after sync.</p>
                <Link
                  to="/"
                  className="px-5 py-2 rounded-xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-500 transition-colors"
                >
                  Back home
                </Link>
              </div>
            ) : (
              <>
                <div
                  className={`${PRODUCT_GRID_CLASS_WITH_SIDEBAR} transition-opacity ${busy ? 'opacity-60' : 'opacity-100'}`}
                >
                  {results.map((product, i) => (
                    <ProductCard key={product.id} product={product} index={i} />
                  ))}
                </div>

                {lastPage > 1 && (
                  <Pagination
                    page={Number(searchParams.get('page') ?? 1)}
                    lastPage={lastPage}
                    onPage={(p) => pushParams({ page: String(p) })}
                  />
                )}
              </>
            )}
          </div>
        </div>
      </div>
    </div>
  )
}

function FilterSidebar({ minPrice, setMinPrice, maxPrice, setMaxPrice, onApply, onClear }) {
  const hasFilters = minPrice || maxPrice
  return (
    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-5">
      <div className="flex items-center justify-between">
        <p className="text-xs font-bold uppercase tracking-widest text-gray-500">Filters</p>
        {hasFilters && (
          <button type="button" onClick={onClear} className="text-xs text-primary-500 hover:text-primary-700 font-semibold">
            Clear
          </button>
        )}
      </div>
      <div>
        <p className="text-xs font-semibold text-gray-700 mb-2">Price range (₹)</p>
        <div className="flex gap-2">
          <input
            type="number"
            placeholder="Min"
            value={minPrice}
            onChange={(e) => setMinPrice(e.target.value)}
            min="0"
            className="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm outline-none focus:border-primary-400"
          />
          <input
            type="number"
            placeholder="Max"
            value={maxPrice}
            onChange={(e) => setMaxPrice(e.target.value)}
            min="0"
            className="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm outline-none focus:border-primary-400"
          />
        </div>
        <button
          type="button"
          onClick={onApply}
          className="mt-2 w-full py-1.5 rounded-lg bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-colors"
        >
          Apply
        </button>
      </div>
    </div>
  )
}

function Pagination({ page, lastPage, onPage }) {
  const pages = []
  const start = Math.max(1, page - 2)
  const end = Math.min(lastPage, page + 2)
  for (let i = start; i <= end; i++) pages.push(i)

  return (
    <div className="flex items-center justify-center gap-2 mt-10">
      <button
        type="button"
        onClick={() => onPage(page - 1)}
        disabled={page === 1}
        className="w-9 h-9 rounded-xl border border-gray-200 bg-white flex items-center justify-center text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors shadow-sm"
      >
        <ChevronLeft size={16} />
      </button>

      {start > 1 && (
        <>
          <button
            type="button"
            onClick={() => onPage(1)}
            className="w-9 h-9 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors shadow-sm"
          >
            1
          </button>
          {start > 2 && <span className="text-gray-400 text-sm">…</span>}
        </>
      )}

      {pages.map((p) => (
        <button
          key={p}
          type="button"
          onClick={() => onPage(p)}
          className={`w-9 h-9 rounded-xl border text-sm font-semibold transition-colors shadow-sm ${
            p === page
              ? 'bg-primary-600 border-primary-600 text-white'
              : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'
          }`}
        >
          {p}
        </button>
      ))}

      {end < lastPage && (
        <>
          {end < lastPage - 1 && <span className="text-gray-400 text-sm">…</span>}
          <button
            type="button"
            onClick={() => onPage(lastPage)}
            className="w-9 h-9 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors shadow-sm"
          >
            {lastPage}
          </button>
        </>
      )}

      <button
        type="button"
        onClick={() => onPage(page + 1)}
        disabled={page >= lastPage}
        className="w-9 h-9 rounded-xl border border-gray-200 bg-white flex items-center justify-center text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors shadow-sm"
      >
        <ChevronRight size={16} />
      </button>
    </div>
  )
}
