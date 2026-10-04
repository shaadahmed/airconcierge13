import { api } from './http'

export const subregionService = {
  list: params => api.get('/admin/regions/subregions', { query: params }),
  create: data => api.post('/admin/regions/subregions', data),
  update: (id, data) => api.put(`/admin/regions/subregions/${id}`, data),
  remove: id => api.delete(`/admin/regions/subregions/${id}`),
}
