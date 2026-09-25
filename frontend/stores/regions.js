import { defineStore } from 'pinia'
import { regionService } from '@/services/regionService'

export const useRegionsStore = defineStore('regions', {
  state: () => ({ data: [], current: null, loading: false, errors: {} }),
  actions: {
    async load(params = {}) {
      await this.run(async () => {
        this.data = (await regionService.list(params)).data
      })
    },
    async create(payload) {
      return this.run(async () => {
        const region = (await regionService.create(payload)).data

        this.current = region

        return region
      })
    },
    async update(id, payload) {
      return this.run(async () => {
        const region = (await regionService.update(id, payload)).data

        this.current = region

        return region
      })
    },
    async remove(id) {
      return this.run(async () => {
        await regionService.remove(id)
      })
    },
    async run(callback) {
      this.loading = true
      this.errors = {}

      try {
        return await callback()
      }
      catch (error) {
        this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to process regions.'] }
        throw error
      }
      finally {
        this.loading = false
      }
    },
  },
})
