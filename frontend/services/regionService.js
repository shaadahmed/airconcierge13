import { api } from './http'

export const regionService = {
  list: params => api.get('/admin/regions', { query: params }),
  create: data => api.post('/admin/regions', data),
  update: (id, data) => api.put(`/admin/regions/${id}`, data),
  remove: id => api.delete(`/admin/regions/${id}`),
}
