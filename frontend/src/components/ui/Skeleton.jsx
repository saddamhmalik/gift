export function SkeletonCard() {
  return (
    <div className="card flex flex-col">
      <div className="aspect-[4/3] skeleton rounded-none" />
      <div className="p-3 space-y-2">
        <div className="skeleton h-3 w-16 rounded" />
        <div className="skeleton h-4 w-3/4 rounded" />
        <div className="skeleton h-3 w-full rounded" />
        <div className="skeleton h-3 w-1/2 rounded" />
      </div>
    </div>
  )
}

export function SkeletonCategoryCard() {
  return (
    <div className="skeleton rounded-2xl h-28" />
  )
}

export function SkeletonText({ className = '' }) {
  return <div className={`skeleton rounded ${className}`} />
}
