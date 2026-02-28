import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { Tag } from 'lucide-react'
import { getTags } from '../api/tags'

export default function TagsPage() {
  const { data, isLoading } = useQuery({ queryKey: ['tags'], queryFn: getTags, staleTime: 300_000 })
  const tags = data?.data ?? []

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      {/* Header */}
      <div className="flex items-center gap-4 mb-8">
        <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-violet-600 flex items-center justify-center shadow-lg">
          <Tag size={22} className="text-white" />
        </div>
        <div>
          <h1 className="text-2xl font-extrabold text-slate-900 tracking-tight">All Tags</h1>
          <p className="text-sm text-slate-400 mt-0.5">Browse gift cards by tag</p>
        </div>
      </div>

      {/* Tags grid */}
      <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        {isLoading
          ? Array.from({ length: 10 }).map((_, i) => (
              <div key={i} className="skeleton h-14 rounded-2xl" />
            ))
          : tags.map((tag) => (
              <Link
                key={tag.id}
                to={`/tags/${tag.slug}`}
                className="flex items-center gap-3 px-4 py-3.5 rounded-2xl border border-primary-200 bg-primary-50 text-primary-700 font-semibold text-sm hover:bg-primary-100 hover:border-primary-300 hover:-translate-y-1 transition-all duration-200 shadow-sm hover:shadow-md"
              >
                <span className="w-2.5 h-2.5 rounded-full bg-primary-500 flex-shrink-0" />
                {tag.name}
              </Link>
            ))}
        {!isLoading && tags.length === 0 && (
          <p className="col-span-5 text-center text-slate-400 py-16">No tags found.</p>
        )}
      </div>
    </div>
  )
}
