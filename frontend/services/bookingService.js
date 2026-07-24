import { api } from './http'

export const bookingService = {
  list: params => api.get('/admin/bookings', { query: params }),
  get: id => api.get(`/admin/bookings/${id}`),
  create: data => api.post('/admin/bookings', data),
  update: (id, data) => api.put(`/admin/bookings/${id}`, data),
  remove: id => api.delete(`/admin/bookings/${id}`),
  cancel: id => api.post(`/admin/bookings/${id}/cancel`),
}
