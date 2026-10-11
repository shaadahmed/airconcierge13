import { api } from './http'

export const userService = {
  list: params => api.get('/admin/users', { query: params }),
  create: data => api.post('/admin/users', data),
  update: (id, data) => api.put(`/admin/users/${id}`, data),
  remove: id => api.delete(`/admin/users/${id}`),
}
