import { api } from './http'

export const termsService = {
  show: () => api.get('/admin/terms'),
  agree: () => api.post('/admin/terms/agree'),
  disagree: () => api.post('/admin/terms/disagree'),
}
