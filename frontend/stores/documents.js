import { defineStore } from 'pinia'
import { documentService } from '@/services/documentService'

export const useDocumentsStore = defineStore('documents', {
  state: () => ({ data: [], loading: false, errors: {} }),
  actions: {
    async load() {
      await this.run(async () => {
        this.data = (await documentService.list()).data
      })
    },
    async create(payload) {
      return this.run(async () => {
        return (await documentService.create(payload)).data
      })
    },
    async update(id, payload) {
      return this.run(async () => {
        return (await documentService.update(id, payload)).data
      })
    },
    async remove(id) {
      return this.run(async () => {
        await documentService.remove(id)
      })
    },
    async run(callback) {
      this.loading = true
      this.errors = {}

      try {
        return await callback()
      }
      catch (error) {
        this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to process documents.'] }
        throw error
      }
      finally {
        this.loading = false
      }
    },
  },
})
