import { api } from './http'

export const ownerStatementService = {
  index: () => api.get('/admin/owner-statements'),
  report: params => api.get('/admin/owner-statements/report', { query: params }),
  exportUrl: params => {
    const query = new URLSearchParams(
      Object.entries(params).filter(([, value]) => value !== '' && value !== null && value !== undefined),
    ).toString()

    return `/admin/owner-statements/export?${query}`
  },
}
