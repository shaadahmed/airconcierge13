import { api } from './http'

export const cmsService = {
  show: (pageId = 'owner-agreement') => api.get(`/admin/cms/${pageId}`),
  update: (pageId, data) => api.put(`/admin/cms/${pageId}`, data),
}
