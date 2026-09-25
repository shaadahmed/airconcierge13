import { defineStore } from 'pinia'
import { ownerService } from '@/services/ownerService'

export const useOwnersStore = defineStore('owners', {
  state: () => ({ data: [], current: null, loading: false, errors: {} }),
  actions: {
    async load(params = {}) {
      await this.run(async () => {
        this.data = (await ownerService.list(params)).data
      })
    },
    async create(payload) {
      return this.run(async () => {
        const owner = (await ownerService.create(payload)).data

        this.current = owner

        return owner
      })
    },
    async update(id, payload) {
      return this.run(async () => {
        const owner = (await ownerService.update(id, payload)).data

        this.current = owner

        return owner
      })
    },
    async remove(id) {
      return this.run(async () => {
        await ownerService.remove(id)
      })
    },
    async run(callback) {
      this.loading = true
      this.errors = {}

      try {
        return await callback()
      }
      catch (error) {
        this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to process owners.'] }
        throw error
      }
      finally {
        this.loading = false
      }
    },
  },
})
