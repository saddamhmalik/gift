export function SkeletonCard() {
  return (
    <div className="flex flex-col bg-white rounded-2xl border border-slate-200 overflow-hidden">
      <div className="skeleton" style={{ aspectRatio: '16/7' }} />
      <div className="flex items-center justify-between px-4 py-2.5 gap-3">
        <div className="flex-1 space-y-1.5">
          <div className="skeleton h-3.5 w-3/4 rounded" />
          <div className="skeleton h-3 w-1/2 rounded" />
        </div>
        <div className="skeleton h-6 w-20 rounded-full flex-shrink-0" />
      </div>
    </div>
  )
}

export function SkeletonCategoryCard() {
  return (
    <div className="p-[1.5px] rounded-2xl bg-gradient-to-br from-slate-200 via-slate-100 to-slate-200">
      <div className="bg-white rounded-[14px] overflow-hidden">
        <div className="skeleton" style={{ aspectRatio: '16/7' }} />
        <div className="flex items-center justify-between px-4 py-2.5 gap-3">
          <div className="skeleton h-3.5 w-2/3 rounded" />
          <div className="skeleton h-5 w-14 rounded-full flex-shrink-0" />
        </div>
      </div>
    </div>
  )
}

export function SkeletonText({ className = '' }) {
  return <div className={`skeleton rounded ${className}`} />
}
