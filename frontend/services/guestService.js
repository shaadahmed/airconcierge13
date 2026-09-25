import { api } from './http'

export const guestService = {
  list: params => api.get('/admin/guests', { query: params }),
  create: data => api.post('/admin/guests', data),
  update: (id, data) => api.put(`/admin/guests/${id}`, data),
  remove: id => api.delete(`/admin/guests/${id}`),
}
