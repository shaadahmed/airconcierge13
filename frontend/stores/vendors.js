import { defineStore } from 'pinia'
import { vendorService } from '@/services/vendorService'

export const useVendorsStore = defineStore('vendors', {
  state: () => ({ data: [], current: null, loading: false, errors: {} }),
  actions: {
    async load(params = {}) {
      await this.run(async () => {
        this.data = (await vendorService.list(params)).data
      })
    },
    async create(payload) {
      return this.run(async () => {
        const vendor = (await vendorService.create(payload)).data

        this.current = vendor

        return vendor
      })
    },
    async update(id, payload) {
      return this.run(async () => {
        const vendor = (await vendorService.update(id, payload)).data

        this.current = vendor

        return vendor
      })
    },
    async run(callback) {
      this.loading = true
      this.errors = {}

      try {
        return await callback()
      }
      catch (error) {
        this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to process vendors.'] }
        throw error
      }
      finally {
        this.loading = false
      }
    },
  },
})
