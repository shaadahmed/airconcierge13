import { api } from './http'

export const zohoService = {
  status: () => api.get('/admin/zoho'),
  downloadUrl: id => `/admin/zoho/download/${id}`,
}
