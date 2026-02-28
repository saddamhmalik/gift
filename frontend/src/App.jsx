import { BrowserRouter, Routes, Route } from 'react-router-dom'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { AuthProvider } from './contexts/AuthContext'
import { OrderProvider } from './contexts/OrderContext'
import Layout from './components/layout/Layout'

import HomePage           from './pages/HomePage'
import LoginPage          from './pages/LoginPage'
import RegisterPage       from './pages/RegisterPage'
import CategoriesPage     from './pages/CategoriesPage'
import ProductDetailPage  from './pages/ProductDetailPage'
import ProductListPage    from './pages/ProductListPage'
import OrdersPage         from './pages/OrdersPage'
import OrderDetailPage    from './pages/OrderDetailPage'
import PaymentFailurePage from './pages/PaymentFailurePage'
import AboutPage          from './pages/AboutPage'
import PrivacyPolicyPage  from './pages/PrivacyPolicyPage'
import TermsPage          from './pages/TermsPage'
import LoyaltyPage        from './pages/LoyaltyPage'
import TagsPage           from './pages/TagsPage'
import TagPage            from './pages/TagPage'
import NotFoundPage       from './pages/NotFoundPage'

const queryClient = new QueryClient({
  defaultOptions: {
    queries: { retry: 1, refetchOnWindowFocus: false },
  },
})

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <AuthProvider>
          <OrderProvider>
            <Routes>
              {/* Auth — no layout shell */}
              <Route path="/login"    element={<LoginPage />} />
              <Route path="/register" element={<RegisterPage />} />

              {/* Main layout */}
              <Route path="/"                     element={<Layout><HomePage /></Layout>} />
              <Route path="/categories"           element={<Layout><CategoriesPage /></Layout>} />
              <Route path="/categories/:slug"     element={<Layout><CategoriesPage /></Layout>} />
              <Route path="/products/:slug"       element={<Layout><ProductDetailPage /></Layout>} />

              {/* Product list sections */}
              <Route path="/hot-deals"            element={<Layout><ProductListPage /></Layout>} />
              <Route path="/trending"             element={<Layout><ProductListPage /></Layout>} />
              <Route path="/best-sellers"         element={<Layout><ProductListPage /></Layout>} />
              <Route path="/featured"             element={<Layout><ProductListPage /></Layout>} />
              <Route path="/new-arrivals"         element={<Layout><ProductListPage /></Layout>} />

              {/* Orders */}
              <Route path="/orders"               element={<Layout><OrdersPage /></Layout>} />
              <Route path="/orders/:id"           element={<Layout><OrderDetailPage /></Layout>} />

              {/* Payment result */}
              <Route path="/payment/failure"      element={<Layout><PaymentFailurePage /></Layout>} />

              {/* Tags */}
              <Route path="/tags"                 element={<Layout><TagsPage /></Layout>} />
              <Route path="/tags/:slug"           element={<Layout><TagPage /></Layout>} />

              {/* Company pages */}
              <Route path="/about"                element={<Layout><AboutPage /></Layout>} />
              <Route path="/privacy-policy"       element={<Layout><PrivacyPolicyPage /></Layout>} />
              <Route path="/terms"                element={<Layout><TermsPage /></Layout>} />
              <Route path="/loyalty"              element={<Layout><LoyaltyPage /></Layout>} />

              {/* Catch-all */}
              <Route path="*"                     element={<Layout><NotFoundPage /></Layout>} />
            </Routes>
          </OrderProvider>
        </AuthProvider>
      </BrowserRouter>
    </QueryClientProvider>
  )
}
