import { defineStore } from 'pinia'
import { guestService } from '@/services/guestService'

export const useGuestsStore = defineStore('guests', {
  state: () => ({ data: [], current: null, loading: false, errors: {} }),
  actions: {
    async load(params = {}) {
      await this.run(async () => {
        this.data = (await guestService.list(params)).data
      })
    },
    async create(payload) {
      return this.run(async () => {
        const guest = (await guestService.create(payload)).data

        this.current = guest

        return guest
      })
    },
    async update(id, payload) {
      return this.run(async () => {
        const guest = (await guestService.update(id, payload)).data

        this.current = guest

        return guest
      })
    },
    async remove(id) {
      return this.run(async () => {
        await guestService.remove(id)
      })
    },
    async run(callback) {
      this.loading = true
      this.errors = {}

      try {
        return await callback()
      }
      catch (error) {
        this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to process guests.'] }
        throw error
      }
      finally {
        this.loading = false
      }
    },
  },
})
