import { api } from './http'

export const propertyService = {
  list: params => api.get('/admin/properties', { query: params }),
  show: id => api.get(`/admin/properties/${id}`),
  create: data => api.post('/admin/properties', data),
  update: (id, data) => api.put(`/admin/properties/${id}`, data),
  remove: id => api.delete(`/admin/properties/${id}`),
}
