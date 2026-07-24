import { defineStore } from 'pinia'
import { paymentService } from '@/services/paymentService'

export const usePaymentsStore = defineStore('payments', {
  state: () => ({ data: [], propertyPayments: [], loading: false, errors: {} }),
  actions: {
    async load(params = {}) {
      await this.run(async () => {
        this.data = (await paymentService.list(params)).data
      })
    },
    async loadPropertyPayments(params = {}) {
      await this.run(async () => {
        this.propertyPayments = (await paymentService.listPropertyPayments(params)).data
      })
    },
    async create(data) {
      return this.run(() => paymentService.create(data))
    },
    async createPropertyPayment(data) {
      return this.run(() => paymentService.createPropertyPayment(data))
    },
    async run(callback) {
      this.loading = true
      this.errors = {}

      try {
        return await callback()
      }
      catch (error) {
        this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to load payments.'] }
        throw error
      }
      finally {
        this.loading = false
      }
    },
  },
})
