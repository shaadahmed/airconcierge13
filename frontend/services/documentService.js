import { api } from './http'

export const documentService = {
  list: () => api.get('/admin/documents'),
  create: data => {
    if (data instanceof FormData)
      return api.post('/admin/documents', data)

    return api.post('/admin/documents', data)
  },
  update: (id, data) => api.put(`/admin/documents/${id}`, data),
  remove: id => api.delete(`/admin/documents/${id}`),
}
