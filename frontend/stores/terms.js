import { defineStore } from 'pinia'
import { termsService } from '@/services/termsService'

export const useTermsStore = defineStore('terms', {
  state: () => ({ data: null, loading: false, errors: {} }),
  actions: {
    async load() {
      await this.run(async () => {
        this.data = (await termsService.show()).data
      })
    },
    async agree() {
      return this.run(async () => {
        return (await termsService.agree()).data
      })
    },
    async disagree() {
      return this.run(async () => {
        return (await termsService.disagree()).data
      })
    },
    async run(callback) {
      this.loading = true
      this.errors = {}

      try {
        return await callback()
      }
      catch (error) {
        this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to process terms.'] }
        throw error
      }
      finally {
        this.loading = false
      }
    },
  },
})
