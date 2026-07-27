import { defineStore } from 'pinia'
import { adminEntityService } from '@/services/adminEntityService'

export const useAdminEntitiesStore = defineStore('adminEntities', {
  state: () => ({ records: {}, loading: false, errors: {} }),
  actions: {
    async list(endpoint, params = {}) {
      return this.run(async () => {
        const response = await adminEntityService.list(endpoint, params)
        const data = response.data ?? response

        const records = data.data ?? data

        this.records[endpoint] = Array.isArray(records) ? records : [records]
        return this.records[endpoint]
      })
    },
    async create(endpoint, data) {
      return this.run(() => adminEntityService.create(endpoint, data))
    },
    async update(endpoint, id, data) {
      return this.run(() => adminEntityService.update(endpoint, id, data))
    },
    async remove(endpoint, id) {
      return this.run(() => adminEntityService.remove(endpoint, id))
    },
    async run(callback) {
      this.loading = true
      this.errors = {}

      try {
        return await callback()
      }
      catch (error) {
        this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to load records.'] }
        throw error
      }
      finally {
        this.loading = false
      }
    },
  },
})
