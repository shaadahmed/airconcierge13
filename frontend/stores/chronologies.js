import { defineStore } from 'pinia'
import { chronologyService } from '@/services/chronologyService'

export const useChronologiesStore = defineStore('chronologies', {
  state: () => ({ data: [], current: null, loading: false, errors: {} }),
  actions: {
    async load() {
      await this.run(async () => {
        this.data = (await chronologyService.list()).data
      })
    },
    async loadOne(id) {
      await this.run(async () => {
        this.current = (await chronologyService.get(id)).data
      })
    },
    async create(data) {
      return this.run(() => chronologyService.create(data))
    },
    async update(id, data) {
      return this.run(() => chronologyService.update(id, data))
    },
    async run(callback) {
      this.loading = true
      this.errors = {}

      try {
        return await callback()
      }
      catch (error) {
        this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to load chronologies.'] }
        throw error
      }
      finally {
        this.loading = false
      }
    },
  },
})
