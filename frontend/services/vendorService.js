import { api } from './http'

export const vendorService = {
  list: params => api.get('/admin/vendors', { query: params }),
  create: data => api.post('/admin/vendors', data),
  update: (id, data) => api.put(`/admin/vendors/${id}`, data),
}
