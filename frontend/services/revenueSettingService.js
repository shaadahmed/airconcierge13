import { api } from './http'

export const revenueSettingService = {
  list: params => api.get('/admin/settings/revenue', { query: params }),
  create: data => api.post('/admin/settings/revenue', data),
  update: (id, data) => api.put(`/admin/settings/revenue/${id}`, data),
}
