import { api } from './http'

export const managerService = {
  list: params => api.get('/admin/managers', { query: params }),
  create: data => api.post('/admin/managers', data),
  update: (id, data) => api.put(`/admin/managers/${id}`, data),
  remove: id => api.delete(`/admin/managers/${id}`),
}
