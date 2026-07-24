import { defineStore } from 'pinia'
import { zohoService } from '@/services/zohoService'

export const useZohoStore = defineStore('zoho', {
  state: () => ({ data: null, loading: false, errors: {} }),
  actions: {
    async load() {
      this.loading = true
      this.errors = {}

      try {
        this.data = (await zohoService.status()).data
      }
      catch (error) {
        this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to load Zoho status.'] }
      }
      finally {
        this.loading = false
      }
    },
  },
})
