import { api } from './http'

export const authService = {
  ensureCsrf: () => api.get('/sanctum/csrf-cookie'),
  async login(credentials) {
    await this.ensureCsrf()

    return api.post('/login', credentials)
  },
  logout: () => api.post('/logout'),
  profile: () => api.get('/admin/dashboard/profile'),
}
