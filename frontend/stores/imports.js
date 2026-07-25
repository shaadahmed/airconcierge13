import { defineStore } from 'pinia'
import { importService } from '@/services/importService'

export const useImportsStore = defineStore('imports', {
  state: () => ({
    emails: [],
    lastImported: null,
    loading: false,
    errors: {},
  }),
  actions: {
    async loadEmails() {
      await this.run(async () => {
        this.emails = (await importService.listEmails()).data
      })
    },
    async importOwners(rows) {
      return this.run(async () => {
        this.lastImported = await importService.importOwners(rows)

        return this.lastImported
      })
    },
    async importProperties(rows) {
      return this.run(async () => {
        this.lastImported = await importService.importProperties(rows)

        return this.lastImported
      })
    },
    async storeEmail(payload) {
      return this.run(async () => {
        const email = (await importService.storeEmail(payload)).data

        await this.loadEmails()

        return email
      })
    },
    async run(callback) {
      this.loading = true
      this.errors = {}

      try {
        return await callback()
      }
      catch (error) {
        this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to process imports.'] }
        throw error
      }
      finally {
        this.loading = false
      }
    },
  },
})
