import { useState, useEffect, useCallback } from 'react'
import { useSearchParams, Link } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { Search, SlidersHorizontal, X, ChevronLeft, ChevronRight, Loader2, PackageSearch } from 'lucide-react'
import { searchProducts, getCategories } from '../api/products'
import ProductCard from '../components/ui/ProductCard'

/* ─── Skeleton grid ── */
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
  { value: 'popular',    label: 'Most Popular' },
  { value: 'newest',     label: 'Newest First' },
  { value: 'price_asc',  label: 'Price: Low → High' },
  { value: 'price_desc', label: 'Price: High → Low' },
]

export default function SearchPage() {
  const [searchParams, setSearchParams] = useSearchParams()

  // Controlled state mirrors URL params
  const [inputVal,  setInputVal]  = useState(searchParams.get('q') ?? '')
  const [category,  setCategory]  = useState(searchParams.get('category') ?? '')
  const [minPrice,  setMinPrice]  = useState(searchParams.get('min_price') ?? '')
  const [maxPrice,  setMaxPrice]  = useState(searchParams.get('max_price') ?? '')
  const [sort,      setSort]      = useState(searchParams.get('sort') ?? 'popular')
  const [page,      setPage]      = useState(Number(searchParams.get('page') ?? 1))
  const [filtersOpen, setFiltersOpen] = useState(false)

  const q = searchParams.get('q') ?? ''

  // Sync URL → state when user navigates back/forward
  useEffect(() => {
    setInputVal(searchParams.get('q') ?? '')
    setCategory(searchParams.get('category') ?? '')
    setMinPrice(searchParams.get('min_price') ?? '')
    setMaxPrice(searchParams.get('max_price') ?? '')
    setSort(searchParams.get('sort') ?? 'popular')
    setPage(Number(searchParams.get('page') ?? 1))
  }, [searchParams])

  const pushParams = useCallback((overrides = {}) => {
    const next = {
      ...(inputVal  ? { q: inputVal }                 : {}),
      ...(category  ? { category }                    : {}),
      ...(minPrice  ? { min_price: minPrice }         : {}),
      ...(maxPrice  ? { max_price: maxPrice }         : {}),
      ...(sort !== 'popular' ? { sort }               : {}),
      page: '1',
      ...overrides,
    }
    setSearchParams(next)
  }, [inputVal, category, minPrice, maxPrice, sort, setSearchParams])

  // Categories for filter dropdown
  const { data: catData } = useQuery({
    queryKey:  ['categories'],
    queryFn:   getCategories,
    staleTime: 300_000,
  })
  const categories = catData?.data ?? []

  // Main search query
  const { data, isLoading, isFetching } = useQuery({
    queryKey: ['search', q, category, minPrice, maxPrice, sort, page],
    queryFn:  () => searchProducts({
      q,
      category:  searchParams.get('category') ?? undefined,
      min_price: searchParams.get('min_price') ?? undefined,
      max_price: searchParams.get('max_price') ?? undefined,
      sort:      searchParams.get('sort') ?? undefined,
      page,
      per_page:  18,
    }),
    staleTime: 1000 * 30,
    placeholderData: prev => prev,
  })

  const results   = data?.data?.data ?? []
  const meta      = data?.data?.meta ?? {}
  const totalHits = meta.total ?? 0
  const lastPage  = meta.last_page ?? 1

  const handleSearch = (e) => {
    e.preventDefault()
    pushParams({ q: inputVal, page: '1' })
  }

  const clearFilter = (key) => {
    const p = Object.fromEntries(searchParams.entries())
    delete p[key]
    p.page = '1'
    setSearchParams(p)
  }

  const activeFilters = [
    category  && { key: 'category',  label: `Category: ${categories.find(c => c.slug === category)?.name ?? category}` },
    minPrice  && { key: 'min_price', label: `Min ₹${minPrice}` },
    maxPrice  && { key: 'max_price', label: `Max ₹${maxPrice}` },
  ].filter(Boolean)

  const busy = isLoading || isFetching

  return (
    <div className="min-h-screen bg-gray-50/50">

      {/* ── Search hero ── */}
      <div className="bg-gradient-to-br from-surface-950 via-slate-900 to-surface-950 pt-24 pb-10 px-4">
        <div className="max-w-2xl mx-auto text-center mb-6">
          <h1 className="text-2xl sm:text-3xl font-extrabold text-white mb-1">
            {q ? <>Results for <span className="text-primary-400">"{q}"</span></> : 'Browse Gift Cards'}
          </h1>
          {!isLoading && (
            <p className="text-sm text-slate-400 mt-1">
              {q
                ? `${totalHits.toLocaleString()} gift card${totalHits !== 1 ? 's' : ''} found`
                : 'Search by brand, category, or keyword'}
            </p>
          )}
        </div>

        {/* Search bar */}
        <form onSubmit={handleSearch} className="max-w-xl mx-auto flex gap-2">
          <div className="flex-1 flex items-center gap-2 bg-white/10 border border-white/15 rounded-2xl px-4 py-3 focus-within:border-primary-400 transition-all">
            <Search size={16} className="text-slate-400 shrink-0" />
            <input
              type="text"
              autoFocus={!q}
              value={inputVal}
              onChange={e => setInputVal(e.target.value)}
              placeholder="Search gift cards, brands, categories…"
              className="flex-1 bg-transparent outline-none text-white placeholder-slate-500 text-sm"
            />
            {inputVal && (
              <button type="button" onClick={() => { setInputVal(''); pushParams({ q: '', page: '1' }) }}
                className="text-slate-500 hover:text-slate-300 transition-colors">
                <X size={14} />
              </button>
            )}
          </div>
          <button type="submit"
            className="px-5 py-3 rounded-2xl bg-primary-600 hover:bg-primary-500 text-white font-bold text-sm transition-colors shadow-md shadow-primary-900/30">
            Search
          </button>
        </form>
      </div>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="flex flex-col lg:flex-row gap-6">

          {/* ── Sidebar filters (desktop) ── */}
          <aside className="hidden lg:block w-56 shrink-0 space-y-5">
            <FilterPanel
              categories={categories}
              category={category}  setCategory={c => { setCategory(c); pushParams({ category: c, page: '1' }) }}
              minPrice={minPrice}  setMinPrice={setMinPrice}
              maxPrice={maxPrice}  setMaxPrice={setMaxPrice}
              onApplyPrice={() => pushParams({ page: '1' })}
              onClearAll={() => { setCategory(''); setMinPrice(''); setMaxPrice(''); setSearchParams(q ? { q } : {}) }}
            />
          </aside>

          {/* ── Main content ── */}
          <div className="flex-1 min-w-0">

            {/* Toolbar */}
            <div className="flex flex-wrap items-center justify-between gap-3 mb-5">
              <div className="flex items-center gap-2 flex-wrap">
                {/* Mobile filter toggle */}
                <button
                  onClick={() => setFiltersOpen(v => !v)}
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

                {/* Active filter chips */}
                {activeFilters.map(f => (
                  <span key={f.key}
                    className="inline-flex items-center gap-1 bg-primary-50 border border-primary-200 text-primary-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                    {f.label}
                    <button onClick={() => clearFilter(f.key)} className="hover:text-primary-900"><X size={11} /></button>
                  </span>
                ))}
              </div>

              {/* Sort */}
              <select
                value={sort}
                onChange={e => { setSort(e.target.value); pushParams({ sort: e.target.value, page: '1' }) }}
                className="text-sm border border-gray-200 rounded-xl px-3 py-2 bg-white text-gray-700 outline-none focus:border-primary-400 shadow-sm cursor-pointer"
              >
                {SORT_OPTIONS.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
              </select>
            </div>

            {/* Mobile filter panel */}
            {filtersOpen && (
              <div className="lg:hidden mb-5 bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <FilterPanel
                  categories={categories}
                  category={category}  setCategory={c => { setCategory(c); pushParams({ category: c, page: '1' }); setFiltersOpen(false) }}
                  minPrice={minPrice}  setMinPrice={setMinPrice}
                  maxPrice={maxPrice}  setMaxPrice={setMaxPrice}
                  onApplyPrice={() => { pushParams({ page: '1' }); setFiltersOpen(false) }}
                  onClearAll={() => { setCategory(''); setMinPrice(''); setMaxPrice(''); setSearchParams(q ? { q } : {}); setFiltersOpen(false) }}
                />
              </div>
            )}

            {/* Results grid */}
            {isLoading ? (
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                {Array.from({ length: 9 }).map((_, i) => <SkeletonCard key={i} />)}
              </div>
            ) : results.length === 0 ? (
              <EmptyState query={q} />
            ) : (
              <>
                <div className={`grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 transition-opacity ${isFetching ? 'opacity-60' : 'opacity-100'}`}>
                  {results.map((product, i) => (
                    <ProductCard key={product.id} product={product} index={i} />
                  ))}
                </div>

                {/* Pagination */}
                {lastPage > 1 && (
                  <Pagination page={page} lastPage={lastPage} onPage={p => pushParams({ page: String(p) })} />
                )}
              </>
            )}
          </div>
        </div>
      </div>
    </div>
  )
}

/* ─── Filter Panel ── */
function FilterPanel({ categories, category, setCategory, minPrice, setMinPrice, maxPrice, setMaxPrice, onApplyPrice, onClearAll }) {
  const hasFilters = category || minPrice || maxPrice
  return (
    <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-5">
      <div className="flex items-center justify-between">
        <p className="text-xs font-bold uppercase tracking-widest text-gray-500">Filters</p>
        {hasFilters && (
          <button onClick={onClearAll} className="text-xs text-primary-500 hover:text-primary-700 font-semibold">Clear all</button>
        )}
      </div>

      {/* Category */}
      <div>
        <p className="text-xs font-semibold text-gray-700 mb-2">Category</p>
        <div className="space-y-1 max-h-52 overflow-y-auto pr-1">
          <label className="flex items-center gap-2 cursor-pointer group">
            <input type="radio" name="cat" value="" checked={!category}
              onChange={() => setCategory('')}
              className="accent-violet-600" />
            <span className="text-sm text-gray-600 group-hover:text-gray-900">All categories</span>
          </label>
          {categories.map(cat => (
            <label key={cat.id} className="flex items-center gap-2 cursor-pointer group">
              <input type="radio" name="cat" value={cat.slug} checked={category === cat.slug}
                onChange={() => setCategory(cat.slug)}
                className="accent-violet-600" />
              <span className="text-sm text-gray-600 group-hover:text-gray-900 truncate">{cat.name}</span>
            </label>
          ))}
        </div>
      </div>

      {/* Price range */}
      <div>
        <p className="text-xs font-semibold text-gray-700 mb-2">Price Range (₹)</p>
        <div className="flex gap-2">
          <input
            type="number" placeholder="Min" value={minPrice}
            onChange={e => setMinPrice(e.target.value)} min="0"
            className="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm outline-none focus:border-primary-400"
          />
          <input
            type="number" placeholder="Max" value={maxPrice}
            onChange={e => setMaxPrice(e.target.value)} min="0"
            className="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm outline-none focus:border-primary-400"
          />
        </div>
        <button onClick={onApplyPrice}
          className="mt-2 w-full py-1.5 rounded-lg bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-colors">
          Apply
        </button>
      </div>
    </div>
  )
}

/* ─── Empty state ── */
function EmptyState({ query }) {
  return (
    <div className="flex flex-col items-center justify-center py-20 text-center">
      <div className="w-20 h-20 rounded-3xl bg-gray-100 flex items-center justify-center mb-5">
        <PackageSearch size={36} className="text-gray-300" />
      </div>
      <h3 className="text-lg font-bold text-gray-800 mb-2">
        {query ? <>No results for <span className="text-primary-500">"{query}"</span></> : 'No products found'}
      </h3>
      <p className="text-sm text-gray-400 max-w-xs mb-6">
        {query
          ? 'Try different keywords, check your spelling, or browse all categories.'
          : 'Try searching for a brand or gift card.'}
      </p>
      <div className="flex gap-3">
        <Link to="/" className="px-5 py-2 rounded-xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-500 transition-colors">
          Browse Home
        </Link>
        <Link to="/categories" className="px-5 py-2 rounded-xl border border-gray-200 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50 transition-colors">
          All Categories
        </Link>
      </div>
    </div>
  )
}

/* ─── Pagination ── */
function Pagination({ page, lastPage, onPage }) {
  const pages = []
  const start = Math.max(1, page - 2)
  const end   = Math.min(lastPage, page + 2)
  for (let i = start; i <= end; i++) pages.push(i)

  return (
    <div className="flex items-center justify-center gap-2 mt-10">
      <button onClick={() => onPage(page - 1)} disabled={page === 1}
        className="w-9 h-9 rounded-xl border border-gray-200 bg-white flex items-center justify-center text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors shadow-sm">
        <ChevronLeft size={16} />
      </button>

      {start > 1 && (
        <>
          <button onClick={() => onPage(1)} className="w-9 h-9 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">1</button>
          {start > 2 && <span className="text-gray-400 text-sm">…</span>}
        </>
      )}

      {pages.map(p => (
        <button key={p} onClick={() => onPage(p)}
          className={`w-9 h-9 rounded-xl border text-sm font-semibold transition-colors shadow-sm ${
            p === page
              ? 'bg-primary-600 border-primary-600 text-white shadow-primary-200'
              : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'
          }`}>
          {p}
        </button>
      ))}

      {end < lastPage && (
        <>
          {end < lastPage - 1 && <span className="text-gray-400 text-sm">…</span>}
          <button onClick={() => onPage(lastPage)} className="w-9 h-9 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">{lastPage}</button>
        </>
      )}

      <button onClick={() => onPage(page + 1)} disabled={page === lastPage}
        className="w-9 h-9 rounded-xl border border-gray-200 bg-white flex items-center justify-center text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors shadow-sm">
        <ChevronRight size={16} />
      </button>
    </div>
  )
}
