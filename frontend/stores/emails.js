import { defineStore } from 'pinia'
import { emailService } from '@/services/emailService'

export const useEmailsStore = defineStore('emails', {
  state: () => ({ data: null, loading: false, errors: {} }),
  actions: {
    async load() {
      await this.run(async () => {
        this.data = (await emailService.form()).data
      })
    },
    async send(data) {
      return this.run(() => emailService.send(data))
    },
    async run(callback) {
      this.loading = true
      this.errors = {}

      try {
        return await callback()
      }
      catch (error) {
        this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to send email.'] }
        throw error
      }
      finally {
        this.loading = false
      }
    },
  },
})
