import { api } from './http'

export const emailService = {
  form: () => api.get('/admin/send-emails/create'),
  send: data => api.post('/admin/send-emails', data),
}
