import { defineStore } from 'pinia'
import { dashboardService } from '@/services/dashboardService'

export const useDashboardStore = defineStore('dashboard', {
  state: () => ({ data: { stats: {}, revenue: [] }, loading: false, errors: {} }),
  actions: {
    async load(params = {}) {
      this.loading = true
      this.errors = {}

      try {
        const [stats, revenue] = await Promise.all([
          dashboardService.stats(),
          dashboardService.revenueChart(params),
        ])

        this.data = { stats: stats.data, revenue: revenue.data }
      }
      catch (error) {
        this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to load dashboard.'] }
      }
      finally {
        this.loading = false
      }
    },
  },
})
