import { Link } from 'react-router-dom'

export default function NotFoundPage() {
  return (
    <div className="min-h-[70vh] flex flex-col items-center justify-center text-center px-4">
      <div className="text-8xl mb-6">🎁</div>
      <h1 className="text-5xl font-extrabold text-gray-900 mb-3">404</h1>
      <h2 className="text-xl font-semibold text-gray-700 mb-2">Page Not Found</h2>
      <p className="text-gray-500 mb-8 max-w-xs">
        Looks like this gift got lost in transit. Let's get you back home.
      </p>
      <Link to="/" className="btn-primary">
        Go to Homepage
      </Link>
    </div>
  )
}
