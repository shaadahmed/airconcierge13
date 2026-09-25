import { defineStore } from 'pinia'
import { propertyAuditService } from '@/services/propertyAuditService'

export const usePropertyAuditsStore = defineStore('propertyAudits', {
  state: () => ({ data: [], current: null, loading: false, errors: {} }),
  actions: {
    async load(params = {}) {
      await this.run(async () => {
        this.data = (await propertyAuditService.list(params)).data
      })
    },
    async create(payload) {
      return this.run(async () => {
        const audit = (await propertyAuditService.create(payload)).data

        this.current = audit

        return audit
      })
    },
    async run(callback) {
      this.loading = true
      this.errors = {}

      try {
        return await callback()
      }
      catch (error) {
        this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to process property audits.'] }
        throw error
      }
      finally {
        this.loading = false
      }
    },
  },
})
