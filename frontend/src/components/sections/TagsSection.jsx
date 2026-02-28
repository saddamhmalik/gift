import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { Tag, ArrowRight } from 'lucide-react'
import { getTags } from '../../api/tags'

export default function TagsSection() {
  const { data, isLoading } = useQuery({
    queryKey: ['tags'],
    queryFn:  getTags,
    staleTime: 300_000,
  })

  const tags = data?.data ?? []

  if (!isLoading && tags.length === 0) return null

  return (
    <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

      {/* Header */}
      <div className="flex items-center justify-between mb-5">
        <div className="flex items-center gap-3">
          <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-500 to-violet-600 flex items-center justify-center shadow-sm">
            <Tag size={16} className="text-white" />
          </div>
          <h2 className="text-[18px] font-extrabold text-slate-900 tracking-tight">Shop by Tags</h2>
        </div>
        <Link to="/tags" className="flex items-center gap-1 text-sm font-bold text-primary-600 hover:text-primary-700 group transition-colors">
          View all <ArrowRight size={13} className="group-hover:translate-x-0.5 transition-transform" />
        </Link>
      </div>

      {/* Tag chips */}
      <div className="flex flex-wrap gap-3">
        {isLoading
          ? Array.from({ length: 8 }).map((_, i) => (
              <div key={i} className="skeleton h-10 w-24 rounded-full" />
            ))
          : tags.map(tag => (
              <Link
                key={tag.id}
                to={`/tags/${tag.slug}`}
                className="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-primary-200 bg-primary-50 text-primary-700 text-sm font-semibold hover:bg-primary-100 hover:border-primary-300 hover:-translate-y-0.5 transition-all duration-200 shadow-sm"
              >
                <span className="w-1.5 h-1.5 rounded-full bg-primary-500 flex-shrink-0" />
                {tag.name}
              </Link>
            ))
        }
      </div>
    </section>
  )
}
