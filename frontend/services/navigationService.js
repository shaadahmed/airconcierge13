import { api } from './http'

export const navigationService = {
  list: () => api.get('/admin/navigation'),
}
