import api from './client'

export const getCategories       = ()           => api.get('/categories').then(r => r.data)
export const getCategoryBySlug   = (slug)       => api.get(`/categories/${slug}`).then(r => r.data)
export const getHotDeals         = ()           => api.get('/products/hot-deals').then(r => r.data)
export const getTrending         = ()           => api.get('/products/trending').then(r => r.data)
export const getBestSellers      = ()           => api.get('/products/best-sellers').then(r => r.data)
export const getFeatured         = ()           => api.get('/products/featured').then(r => r.data)
export const getNewArrivals      = ()           => api.get('/products/new-arrivals').then(r => r.data)
export const getProduct          = (product)   => api.get(`/products/${product}`).then(r => r.data)
