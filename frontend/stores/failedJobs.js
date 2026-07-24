import { defineStore } from 'pinia'
import { failedJobService } from '@/services/failedJobService'

export const useFailedJobsStore = defineStore('failedJobs', {
  state: () => ({ data: [], loading: false, errors: {} }),
  actions: {
    async load(params = {}) {
      await this.run(async () => {
        this.data = (await failedJobService.list(params)).data
      })
    },
    async retry(uuid) {
      await this.run(() => failedJobService.retry(uuid))
      await this.load()
    },
    async retryAll() {
      await this.run(() => failedJobService.retryAll())
      await this.load()
    },
    async remove(uuid) {
      await this.run(() => failedJobService.remove(uuid))
      await this.load()
    },
    async run(callback) {
      this.loading = true
      this.errors = {}

      try {
        return await callback()
      }
      catch (error) {
        this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to load failed jobs.'] }
        throw error
      }
      finally {
        this.loading = false
      }
    },
  },
})
