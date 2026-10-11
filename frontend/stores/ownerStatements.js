import { defineStore } from 'pinia'
import {
  buildMockOwnerStatementReport,
  MOCK_OWNER_STATEMENT_META,
} from '@/mocks/owner-statement'
import { ownerStatementService } from '@/services/ownerStatementService'
import { runStoreAction } from '@/stores/mockHelpers'

export const useOwnerStatementsStore = defineStore('ownerStatements', {
  state: () => ({
    meta: null,
    report: null,
    loading: false,
    errors: {},
    usingMocks: false,
  }),
  actions: {
    async loadMeta() {
      await runStoreAction(this, async () => {
        try {
          const response = await ownerStatementService.index()
          const meta = response.data?.data || response.data

          if (meta?.properties?.length) {
            this.meta = meta
            this.usingMocks = false

            return
          }
        }
        catch {
          // Fall through to UI preview fixtures.
        }

        this.meta = structuredClone(MOCK_OWNER_STATEMENT_META)
        this.usingMocks = true
      }, 'errors', 'Unable to load owner statements.')
    },
    async loadReport(params) {
      await runStoreAction(this, async () => {
        if (this.usingMocks) {
          this.report = buildMockOwnerStatementReport(params)

          return this.report
        }

        try {
          const response = await ownerStatementService.report(params)
          const report = response.data?.data || response.data

          if (report) {
            this.report = report
            this.usingMocks = false

            return this.report
          }
        }
        catch {
          // Fall through to UI preview fixtures.
        }

        this.usingMocks = true
        this.report = buildMockOwnerStatementReport(params)

        return this.report
      }, 'errors', 'Unable to load owner statements.')
    },
  },
})
