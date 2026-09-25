import { api } from './http'

export const countryService = {
  list: params => api.get('/admin/countries', { query: params }),
  create: data => api.post('/admin/countries', data),
  update: (id, data) => api.put(`/admin/countries/${id}`, data),
  remove: id => api.delete(`/admin/countries/${id}`),
  createState: (countryId, data) => api.post(`/admin/countries/${countryId}/states`, data),
  updateState: (countryId, stateId, data) => api.put(`/admin/countries/${countryId}/states/${stateId}`, data),
  removeState: (countryId, stateId) => api.delete(`/admin/countries/${countryId}/states/${stateId}`),
}
