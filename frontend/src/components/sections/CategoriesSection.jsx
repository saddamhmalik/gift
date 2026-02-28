import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { ArrowRight, LayoutGrid } from 'lucide-react'
import { getCategories } from '../../api/products'

const THEMES = [
  {
    img: 'from-pink-500    to-rose-600',
    border: 'from-pink-300    via-rose-200    to-orange-300',
    shadow: 'rgba(244,63,94,0.2)',
  },
  {
    img: 'from-violet-500  to-purple-600',
    border: 'from-violet-300  via-purple-200  to-pink-300',
    shadow: 'rgba(139,92,246,0.2)',
  },
  {
    img: 'from-blue-500    to-cyan-600',
    border: 'from-blue-300    via-cyan-200    to-teal-300',
    shadow: 'rgba(14,165,233,0.2)',
  },
  {
    img: 'from-emerald-500 to-teal-600',
    border: 'from-emerald-300 via-teal-200   to-cyan-300',
    shadow: 'rgba(16,185,129,0.2)',
  },
  {
    img: 'from-amber-400   to-orange-500',
    border: 'from-amber-300   via-orange-200 to-rose-300',
    shadow: 'rgba(245,158,11,0.2)',
  },
  {
    img: 'from-red-500     to-pink-600',
    border: 'from-red-300     via-pink-200   to-violet-300',
    shadow: 'rgba(239,68,68,0.2)',
  },
  {
    img: 'from-indigo-500  to-blue-600',
    border: 'from-indigo-300  via-blue-200   to-sky-300',
    shadow: 'rgba(99,102,241,0.2)',
  },
  {
    img: 'from-teal-500    to-cyan-600',
    border: 'from-teal-300    via-cyan-200   to-blue-300',
    shadow: 'rgba(20,184,166,0.2)',
  },
]

const EMOJI = ['🎁', '🛍️', '🍕', '🎮', '✈️', '💆', '💻', '⚽', '📚', '👗', '💎', '🏠']

function SkeletonCategoryCard() {
  return (
    <div className="p-[1.5px] rounded-2xl bg-gradient-to-br from-slate-200 via-slate-100 to-slate-200">
      <div className="bg-white rounded-[14px] overflow-hidden">
        <div className="skeleton" style={{ aspectRatio: '16/7' }} />
        <div className="flex items-center justify-between px-4 py-2.5 gap-3">
          <div className="flex-1 space-y-1.5">
            <div className="skeleton h-3.5 w-2/3 rounded" />
          </div>
          <div className="skeleton h-5 w-14 rounded-full flex-shrink-0" />
        </div>
      </div>
    </div>
  )
}

export default function CategoriesSection() {
  const { data, isLoading } = useQuery({
    queryKey: ['categories'],
    queryFn: getCategories,
    staleTime: 300_000,
  })
  const categories = data?.data ?? []

  return (
    <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      {/* Header */}
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center gap-3">
          <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-500 to-violet-600 flex items-center justify-center shadow-sm flex-shrink-0">
            <LayoutGrid size={17} className="text-white" />
          </div>
          <h2 className="text-[18px] font-extrabold text-slate-900 tracking-tight">
            Shop by Category
          </h2>
        </div>
        <Link
          to="/categories"
          className="flex items-center gap-1 text-sm font-bold text-primary-600 hover:text-primary-700 group transition-colors"
        >
          View all{' '}
          <ArrowRight size={13} className="group-hover:translate-x-0.5 transition-transform" />
        </Link>
      </div>

      {/* Grid — 3 col matching product section */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
        {isLoading
          ? Array.from({ length: 6 }).map((_, i) => <SkeletonCategoryCard key={i} />)
          : categories.slice(0, 6).map((cat, i) => {
              const theme = THEMES[i % THEMES.length]
              const emoji = EMOJI[i % EMOJI.length]
              return (
                <div
                  key={cat.id}
                  className={`group p-[1.5px] rounded-2xl bg-gradient-to-br ${theme.border} transition-all duration-300 ease-out hover:-translate-y-1.5`}
                  style={{ boxShadow: `0 4px 20px ${theme.shadow}` }}
                  onMouseEnter={(e) => {
                    e.currentTarget.style.boxShadow = `0 12px 36px ${theme.shadow}`
                  }}
                  onMouseLeave={(e) => {
                    e.currentTarget.style.boxShadow = `0 4px 20px ${theme.shadow}`
                  }}
                >
                  <Link
                    to={`/categories/${cat.slug}`}
                    className="flex flex-col bg-white rounded-[14px] overflow-hidden h-full"
                  >
                    {/* Image panel — same 16:7 ratio */}
                    <div
                      className={`relative overflow-hidden rounded-t-[14px] bg-gradient-to-br ${theme.img} flex items-center justify-center`}
                      style={{ aspectRatio: '16/7' }}
                    >
                      {cat.image_url || cat.thumbnail_url ? (
                        <img
                          src={cat.image_url || cat.thumbnail_url}
                          alt={cat.name}
                          className="w-full h-full object-cover group-hover:scale-[1.04] transition-transform duration-500 ease-out"
                          loading="lazy"
                          decoding="async"
                        />
                      ) : (
                        <>
                          <div className="absolute -top-6 -right-6 w-24 h-24 rounded-full bg-white/10" />
                          <div className="absolute -bottom-6 -left-6 w-28 h-28 rounded-full bg-white/10" />
                          <span className="relative text-4xl group-hover:scale-110 transition-transform duration-300">
                            {emoji}
                          </span>
                        </>
                      )}
                      {/* Top gradient overlay */}
                      <div className="absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-transparent" />
                    </div>

                    {/* Footer row */}
                    <div className="flex items-center justify-between px-4 py-2.5 gap-3">
                      <h3 className="text-[13px] font-bold text-slate-900 truncate leading-tight group-hover:text-primary-700 transition-colors duration-200">
                        {cat.name}
                      </h3>
                      <span className="inline-flex items-center gap-1 bg-primary-50 text-primary-600 border border-primary-200 text-[10px] font-semibold px-2.5 py-1 rounded-full flex-shrink-0 whitespace-nowrap">
                        Shop →
                      </span>
                    </div>
                  </Link>
                </div>
              )
            })}
      </div>
    </section>
  )
}
