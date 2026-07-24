import { defineStore } from 'pinia'
import { reportService } from '@/services/reportService'

export const useReportsStore = defineStore('reports', {
  state: () => ({ data: { summary: null, tot: null, metrics: null }, loading: false, errors: {} }),
  actions: {
    async load(params = {}) {
      this.loading = true
      this.errors = {}

      try {
        const [summary, tot, metrics] = await Promise.all([
          reportService.summary(params),
          reportService.tot(params),
          reportService.metrics(params),
        ])

        this.data = { summary: summary.data, tot: tot.data, metrics: metrics.data }
      }
      catch (error) {
        this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to load reports.'] }
      }
      finally {
        this.loading = false
      }
    },
  },
})
