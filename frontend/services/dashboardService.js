import { api } from './http'

export const dashboardService = {
  stats: () => api.get('/admin/dashboard/stats'),
  revenueChart: params => api.get('/admin/dashboard/revenue-chart', { query: params }),
  profile: () => api.get('/admin/dashboard/profile'),
  ownerStatement: ownerId => api.get(`/admin/dashboard/owners/${ownerId}/statement`),
}
