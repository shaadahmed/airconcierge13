import { api } from './http'

export const chronologyService = {
  list: () => api.get('/admin/chronologies'),
  get: id => api.get(`/admin/chronologies/${id}`),
  create: data => api.post('/admin/chronologies', data),
  update: (id, data) => api.put(`/admin/chronologies/${id}`, data),
  remove: id => api.delete(`/admin/chronologies/${id}`),
  copy: id => api.post(`/admin/chronologies/${id}/copy`),
  templates: params => api.get('/admin/chronologies/templates', { query: params }),
  documents: params => api.get('/admin/chronologies/documents', { query: params }),
  checkName: params => api.get('/admin/chronologies/check-name', { query: params }),
  createOrder: (id, data) => api.post(`/admin/chronologies/${id}/orders`, data),
  updateOrder: (id, orderId, data) => api.put(`/admin/chronologies/${id}/orders/${orderId}`, data),
  removeOrder: (id, orderId) => api.delete(`/admin/chronologies/${id}/orders/${orderId}`),
  owners: id => api.get(`/admin/chronologies/${id}/owners`),
  saveOwnerEmails: (id, data) => api.post(`/admin/chronologies/${id}/owner-emails`, data),
}
