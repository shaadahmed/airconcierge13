import { api } from './http'

export const ownerBlockService = {
  list: params => api.get('/admin/dashboard/ownerblock/all', { query: params }),
  get: id => api.get(`/admin/dashboard/ownerblock/${id}`),
  create: data => api.post('/admin/dashboard/ownerblock/new', data),
  update: (id, data) => api.put(`/admin/dashboard/ownerblock/${id}`, data),
  remove: id => api.delete(`/admin/dashboard/ownerblock/${id}`),
}
