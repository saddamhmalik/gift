export function SkeletonCard() {
  return (
    <div className="flex flex-col bg-white rounded-2xl border border-slate-200 overflow-hidden">
      <div className="skeleton w-full" style={{ aspectRatio: '16/9' }} />
      <div className="flex items-center justify-between gap-2 px-4 py-2.5">
        <div className="skeleton h-4 w-3/5 rounded" />
        <div className="skeleton h-3 w-10 rounded" />
      </div>
    </div>
  )
}

export function SkeletonCategoryCard() {
  return <SkeletonCard />
}

export function SkeletonText({ className = '' }) {
  return <div className={`skeleton rounded ${className}`} />
}
