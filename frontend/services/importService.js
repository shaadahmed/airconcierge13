import { api } from './http'

export const importService = {
  importOwners: rows => api.post('/admin/imports/owners', { rows }),
  importProperties: rows => api.post('/admin/imports/properties', { rows }),
  listEmails: () => api.get('/admin/imports/emails'),
  storeEmail: data => api.post('/admin/imports/emails', data),
}
