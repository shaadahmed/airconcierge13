import { api } from './http'

export const ownerService = {
  list: params => api.get('/admin/owners', { query: params }),
  create: data => api.post('/admin/owners', data),
  update: (id, data) => api.put(`/admin/owners/${id}`, data),
  remove: id => api.delete(`/admin/owners/${id}`),
}
