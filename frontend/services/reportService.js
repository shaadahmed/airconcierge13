import { api } from './http'

export const reportService = {
  summary: params => api.get('/admin/reports', { query: params }),
  tot: params => api.get('/admin/reports/tot', { query: params }),
  metrics: params => api.get('/admin/reports/metrics', { query: params }),
}
