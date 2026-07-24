import { defineStore } from 'pinia'
import { bookingService } from '@/services/bookingService'

export const useBookingsStore = defineStore('bookings', {
  state: () => ({ data: [], current: null, loading: false, errors: {} }),
  actions: {
    async load(params = {}) {
      await this.run(async () => {
        this.data = (await bookingService.list(params)).data
      })
    },
    async loadOne(id) {
      await this.run(async () => {
        this.current = (await bookingService.get(id)).data
      })
    },
    async save(id, data) {
      return this.run(async () => {
        this.current = (await bookingService.update(id, data)).data

        return this.current
      })
    },
    async run(callback) {
      this.loading = true
      this.errors = {}

      try {
        return await callback()
      }
      catch (error) {
        this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to load bookings.'] }
        throw error
      }
      finally {
        this.loading = false
      }
    },
  },
})
