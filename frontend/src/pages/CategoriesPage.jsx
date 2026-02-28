import { useQuery } from '@tanstack/react-query'
import { Link, useParams } from 'react-router-dom'
import { LayoutGrid, ArrowLeft, Loader2 } from 'lucide-react'
import { getCategories, getCategoryBySlug } from '../api/products'
import ProductCard from '../components/ui/ProductCard'
import { SkeletonCard, SkeletonCategoryCard } from '../components/ui/Skeleton'

const GRADIENTS = [
  'from-pink-400 to-rose-500',
  'from-violet-400 to-purple-500',
  'from-blue-400 to-cyan-500',
  'from-emerald-400 to-green-500',
  'from-amber-400 to-orange-500',
  'from-red-400 to-pink-500',
  'from-indigo-400 to-blue-500',
  'from-teal-400 to-cyan-500',
]
const EMOJIS = ['🎁', '🛍️', '🍕', '🎮', '✈️', '💆', '💻', '⚽', '📚', '👗', '💎', '🏠']

export default function CategoriesPage() {
  const { slug } = useParams()

  const { data: catsData, isLoading: catsLoading } = useQuery({
    queryKey: ['categories'],
    queryFn: getCategories,
    staleTime: 1000 * 60 * 5,
  })

  const { data: catData, isLoading: catLoading } = useQuery({
    queryKey: ['category', slug],
    queryFn: () => getCategoryBySlug(slug),
    enabled: !!slug,
    staleTime: 1000 * 60 * 5,
  })

  const categories = catsData?.data ?? []
  const category = catData?.data
  const products = category?.products ?? []

  if (slug) {
    return (
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <Link
          to="/categories"
          className="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-primary-500 mb-6 transition-colors"
        >
          <ArrowLeft size={15} /> All Categories
        </Link>

        {catLoading ? (
          <div className="flex items-center justify-center py-20">
            <Loader2 size={28} className="animate-spin text-primary-500" />
          </div>
        ) : (
          <>
            <div className="mb-8">
              <h1 className="text-3xl font-extrabold text-gray-900">{category?.name}</h1>
              {category?.short_description && (
                <p className="text-gray-500 mt-1">{category.short_description}</p>
              )}
            </div>
            {products.length === 0 ? (
              <div className="text-center py-16 text-gray-400">
                <div className="text-5xl mb-3">📭</div>
                <p>No products in this category yet.</p>
              </div>
            ) : (
              <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                {products.map((p) => (
                  <ProductCard key={p.id} product={p} />
                ))}
              </div>
            )}
          </>
        )}
      </div>
    )
  }

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div className="mb-8">
        <h1 className="text-3xl font-extrabold text-gray-900 flex items-center gap-2">
          <LayoutGrid className="text-primary-500" size={28} /> All Categories
        </h1>
        <p className="text-gray-500 mt-1">Browse by category to find the perfect gift card</p>
      </div>

      <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        {catsLoading
          ? Array.from({ length: 12 }).map((_, i) => <SkeletonCategoryCard key={i} />)
          : categories.map((cat, i) => {
              const gradient = GRADIENTS[i % GRADIENTS.length]
              const emoji = EMOJIS[i % EMOJIS.length]
              return (
                <Link
                  key={cat.id}
                  to={`/categories/${cat.slug}`}
                  className="group rounded-2xl overflow-hidden"
                >
                  <div
                    className={`h-32 bg-gradient-to-br ${gradient} flex flex-col items-center justify-center gap-2 p-3 transition-transform duration-300 group-hover:scale-105`}
                  >
                    {cat.thumbnail_url || cat.image_url ? (
                      <img
                        src={cat.thumbnail_url || cat.image_url}
                        alt={cat.name}
                        className="w-12 h-12 object-cover rounded-full border-2 border-white/40"
                      />
                    ) : (
                      <span className="text-4xl">{emoji}</span>
                    )}
                    <span className="text-white text-xs font-bold text-center leading-tight drop-shadow">
                      {cat.name}
                    </span>
                    {cat.subcategories_count > 0 && (
                      <span className="text-white/70 text-[10px]">
                        {cat.subcategories_count} sub-categories
                      </span>
                    )}
                  </div>
                </Link>
              )
            })}
      </div>
    </div>
  )
}
