import { api } from './http'

export const adminEntityService = {
  list: (endpoint, params) => api.get(endpoint, { query: params }),
  create: (endpoint, data) => api.post(endpoint, data),
  update: (endpoint, id, data) => api.put(`${endpoint}/${id}`, data),
  remove: (endpoint, id) => api.delete(`${endpoint}/${id}`),
}
