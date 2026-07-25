import { defineStore } from 'pinia'
import { ownerStatementService } from '@/services/ownerStatementService'

export const useOwnerStatementsStore = defineStore('ownerStatements', {
  state: () => ({
    meta: null,
    report: null,
    loading: false,
    errors: {},
  }),
  actions: {
    async loadMeta() {
      await this.run(async () => {
        this.meta = (await ownerStatementService.index()).data
      })
    },
    async loadReport(params) {
      await this.run(async () => {
        this.report = (await ownerStatementService.report(params)).data
      })
    },
    async run(callback) {
      this.loading = true
      this.errors = {}

      try {
        return await callback()
      }
      catch (error) {
        this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to load owner statements.'] }
        throw error
      }
      finally {
        this.loading = false
      }
    },
  },
})
