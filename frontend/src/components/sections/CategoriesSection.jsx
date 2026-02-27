import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { ArrowRight, LayoutGrid } from 'lucide-react'
import { getCategories } from '../../api/products'
import { SkeletonCategoryCard } from '../ui/Skeleton'

const DEFAULT_COLORS = [
  'from-pink-400 to-rose-500',
  'from-violet-400 to-purple-500',
  'from-blue-400 to-cyan-500',
  'from-emerald-400 to-green-500',
  'from-amber-400 to-orange-500',
  'from-red-400 to-pink-500',
  'from-indigo-400 to-blue-500',
  'from-teal-400 to-cyan-500',
]

const EMOJI_MAP = ['🎁','🛍️','🍕','🎮','✈️','💆','💻','⚽','📚','👗','💎','🏠']

export default function CategoriesSection() {
  const { data, isLoading } = useQuery({ queryKey: ['categories'], queryFn: getCategories, staleTime: 1000 * 60 * 5 })
  const categories = data?.data ?? []

  return (
    <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div className="flex items-center justify-between mb-6">
        <div>
          <h2 className="section-title flex items-center gap-2"><LayoutGrid size={22} className="text-primary-500" /> Shop by Category</h2>
          <p className="text-sm text-gray-500 mt-0.5">Find the perfect gift by category</p>
        </div>
        <Link to="/categories" className="text-sm font-semibold text-primary-500 hover:text-primary-600 flex items-center gap-1 transition-colors">
          View all <ArrowRight size={14} />
        </Link>
      </div>

      <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        {isLoading
          ? Array.from({length: 6}).map((_, i) => <SkeletonCategoryCard key={i} />)
          : categories.slice(0, 12).map((cat, i) => {
              const gradient = DEFAULT_COLORS[i % DEFAULT_COLORS.length]
              const emoji    = EMOJI_MAP[i % EMOJI_MAP.length]
              return (
                <Link
                  key={cat.id}
                  to={`/categories/${cat.slug}`}
                  className="group relative rounded-2xl overflow-hidden cursor-pointer"
                >
                  <div className={`h-28 bg-gradient-to-br ${gradient} flex flex-col items-center justify-center gap-2 p-3 transition-transform duration-300 group-hover:scale-105`}>
                    {cat.thumbnail_url || cat.image_url
                      ? <img src={cat.thumbnail_url || cat.image_url} alt={cat.name} className="w-10 h-10 object-cover rounded-full border-2 border-white/40" />
                      : <span className="text-3xl">{emoji}</span>
                    }
                    <span className="text-white text-xs font-bold text-center leading-tight drop-shadow">{cat.name}</span>
                  </div>
                </Link>
              )
            })
        }
      </div>
    </section>
  )
}
