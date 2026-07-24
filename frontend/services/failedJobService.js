import { api } from './http'

export const failedJobService = {
  list: params => api.get('/admin/failed-jobs', { query: params }),
  retryAll: () => api.post('/admin/failed-jobs/retry-all'),
  retry: uuid => api.post(`/admin/failed-jobs/${uuid}/retry`),
  remove: uuid => api.delete(`/admin/failed-jobs/${uuid}`),
}
