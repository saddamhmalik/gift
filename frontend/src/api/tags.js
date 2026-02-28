import api from './client'

export const getTags = () =>
  api.get('/tags').then(r => r.data)

export const getTagProducts = (slug, page = 1, perPage = 12) =>
  api.get(`/tags/${slug}`, { params: { page, per_page: perPage } }).then(r => r.data)
