import { api } from './http'

export const propertyAuditService = {
  list: params => api.get('/admin/properties/audit/list', { query: params }),
  create: data => api.post('/admin/properties/audit', data),
}
