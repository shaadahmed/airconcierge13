import { api } from './http'

export const paymentService = {
  list: params => api.get('/admin/payments', { query: params }),
  create: data => api.post('/admin/payments', data),
  remove: id => api.delete(`/admin/payments/${id}`),
  listPropertyPayments: params => api.get('/admin/property-payments', { query: params }),
  createPropertyPayment: data => api.post('/admin/property-payments', data),
  removePropertyPayment: id => api.delete(`/admin/property-payments/${id}`),
}
