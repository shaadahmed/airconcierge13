import { defineStore } from 'pinia'
import { propertyService } from '@/services/propertyService'

export const usePropertiesStore = defineStore('properties', {
  state: () => ({ data: [], current: null, loading: false, errors: {} }),
  actions: {
    async load(params = {}) {
      await this.run(async () => {
        this.data = (await propertyService.list(params)).data
      })
    },
    async create(payload) {
      return this.run(async () => {
        const property = (await propertyService.create(payload)).data

        this.current = property

        return property
      })
    },
    async update(id, payload) {
      return this.run(async () => {
        const property = (await propertyService.update(id, payload)).data

        this.current = property

        return property
      })
    },
    async remove(id) {
      return this.run(async () => {
        await propertyService.remove(id)
      })
    },
    async run(callback) {
      this.loading = true
      this.errors = {}

      try {
        return await callback()
      }
      catch (error) {
        this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to process properties.'] }
        throw error
      }
      finally {
        this.loading = false
      }
    },
  },
})
