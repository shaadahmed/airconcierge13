import { defineStore } from 'pinia'
import { managerService } from '@/services/managerService'

export const useManagersStore = defineStore('managers', {
  state: () => ({ data: [], current: null, loading: false, errors: {} }),
  actions: {
    async load(params = {}) {
      await this.run(async () => {
        this.data = (await managerService.list(params)).data
      })
    },
    async create(payload) {
      return this.run(async () => {
        const manager = (await managerService.create(payload)).data

        this.current = manager

        return manager
      })
    },
    async update(id, payload) {
      return this.run(async () => {
        const manager = (await managerService.update(id, payload)).data

        this.current = manager

        return manager
      })
    },
    async remove(id) {
      return this.run(async () => {
        await managerService.remove(id)
      })
    },
    async run(callback) {
      this.loading = true
      this.errors = {}

      try {
        return await callback()
      }
      catch (error) {
        this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to process managers.'] }
        throw error
      }
      finally {
        this.loading = false
      }
    },
  },
})
