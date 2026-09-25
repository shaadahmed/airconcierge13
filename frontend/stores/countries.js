import { defineStore } from 'pinia'
import { countryService } from '@/services/countryService'

export const useCountriesStore = defineStore('countries', {
  state: () => ({ data: [], current: null, loading: false, errors: {}, stateErrors: {} }),
  actions: {
    async load(params = {}) {
      await this.run(async () => {
        this.data = (await countryService.list(params)).data
      })
    },
    async create(payload) {
      return this.run(async () => {
        const country = (await countryService.create(payload)).data

        this.current = country

        return country
      })
    },
    async update(id, payload) {
      return this.run(async () => {
        const country = (await countryService.update(id, payload)).data

        this.current = country

        return country
      })
    },
    async remove(id) {
      return this.run(async () => {
        await countryService.remove(id)
      })
    },
    async createState(countryId, payload) {
      return this.runState(async () => {
        return (await countryService.createState(countryId, payload)).data
      })
    },
    async updateState(countryId, stateId, payload) {
      return this.runState(async () => {
        return (await countryService.updateState(countryId, stateId, payload)).data
      })
    },
    async removeState(countryId, stateId) {
      return this.runState(async () => {
        await countryService.removeState(countryId, stateId)
      })
    },
    async run(callback) {
      this.loading = true
      this.errors = {}

      try {
        return await callback()
      }
      catch (error) {
        this.errors = error?.data?.errors || { general: [error?.data?.message || 'Unable to process countries.'] }
        throw error
      }
      finally {
        this.loading = false
      }
    },
    async runState(callback) {
      this.loading = true
      this.stateErrors = {}

      try {
        return await callback()
      }
      catch (error) {
        this.stateErrors = error?.data?.errors || { general: [error?.data?.message || 'Unable to process states.'] }
        throw error
      }
      finally {
        this.loading = false
      }
    },
  },
})
