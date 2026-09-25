import { defineStore } from 'pinia'
import { revenueSettingService } from '@/services/revenueSettingService'

export const useRevenueSettingsStore = defineStore('revenueSettings', {
  state: () => ({ data: [], current: null, loading: false, errors: {} }),
  actions: {
    async load(params = {}) {
      await this.run(async () => {
        this.data = (await revenueSettingService.list(params)).data
      })
    },
    async create(payload) {
      return this.run(async () => {
        const setting = (await revenueSettingService.create(payload)).data

        this.current = setting

        return setting
      })
    },
    async update(id, payload) {
      return this.run(async () => {
        const setting = (await revenueSettingService.update(id, payload)).data

        this.current = setting

        return setting
      })
    },
    async run(callback) {
      this.loading = true
      this.errors = {}

      try {
        return await callback()
      }
      catch (error) {
        this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to process revenue settings.'] }
        throw error
      }
      finally {
        this.loading = false
      }
    },
  },
})
