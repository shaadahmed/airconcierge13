import { defineStore } from 'pinia'
import { authService } from '@/services/authService'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    errors: {},
    loading: false,
  }),
  actions: {
    async fetchUser() {
      this.loading = true

      try {
        const response = await authService.profile()

        this.user = response.data

        return this.user
      }
      catch (error) {
        if (error?.statusCode === 401 || error?.status === 401)
          this.user = null
        else
          this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to load profile.'] }

        return null
      }
      finally {
        this.loading = false
      }
    },
    async login(credentials) {
      this.errors = {}
      this.loading = true

      try {
        await authService.login(credentials)

        return await this.fetchUser()
      }
      catch (error) {
        this.user = null
        this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to sign in.'] }
        throw error
      }
      finally {
        this.loading = false
      }
    },
    async logout() {
      this.loading = true

      try {
        await authService.logout()
      }
      finally {
        this.user = null
        this.loading = false
      }
    },
  },
})
