import { defineStore } from 'pinia'
import { MOCK_COUNTRIES } from '@/mocks/countries'
import { countryService } from '@/services/countryService'
import { mockAwareLoad, mockCreate, mockRemove, mockUpdate, normalizeList, runStoreAction } from '@/stores/mockHelpers'

export const useCountriesStore = defineStore('countries', {
  state: () => ({
    data: [],
    current: null,
    loading: false,
    errors: {},
    stateErrors: {},
    usingMocks: false,
  }),
  actions: {
    async load(params = {}) {
      await runStoreAction(this, () => mockAwareLoad(this, {
        list: () => countryService.list(params),
        mocks: MOCK_COUNTRIES,
        clone: true,
      }), 'errors', 'Unable to process countries.')
    },
    async create(payload) {
      return runStoreAction(this, async () => {
        if (this.usingMocks)
          return mockCreate(this, { id: Date.now(), states: [], ...payload })

        const country = (await countryService.create(payload)).data
        this.current = country

        return country
      }, 'errors', 'Unable to process countries.')
    },
    async update(id, payload) {
      return runStoreAction(this, async () => {
        if (this.usingMocks)
          return mockUpdate(this, id, payload, { states: [] })

        const country = (await countryService.update(id, payload)).data
        this.current = country

        return country
      }, 'errors', 'Unable to process countries.')
    },
    async remove(id) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          mockRemove(this, id)

          return
        }

        await countryService.remove(id)
      }, 'errors', 'Unable to process countries.')
    },
    async createState(countryId, payload) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          const list = normalizeList(this.data)
          const country = list.find(item => String(item.id) === String(countryId))
          if (!country)
            throw new Error('Country not found')

          const state = { id: Date.now(), ...payload }
          country.states = [...(country.states || []), state]
          this.data = { data: [...list] }

          return state
        }

        return (await countryService.createState(countryId, payload)).data
      }, 'stateErrors', 'Unable to process states.')
    },
    async updateState(countryId, stateId, payload) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          const list = normalizeList(this.data)
          const country = list.find(item => String(item.id) === String(countryId))
          if (!country)
            throw new Error('Country not found')

          const states = country.states || []
          const index = states.findIndex(item => String(item.id) === String(stateId))
          const existing = index >= 0 ? states[index] : { id: stateId }
          const state = { ...existing, ...payload, id: existing.id }

          if (index >= 0)
            states[index] = state
          else
            states.push(state)

          country.states = [...states]
          this.data = { data: [...list] }

          return state
        }

        return (await countryService.updateState(countryId, stateId, payload)).data
      }, 'stateErrors', 'Unable to process states.')
    },
    async removeState(countryId, stateId) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          const list = normalizeList(this.data)
          const country = list.find(item => String(item.id) === String(countryId))
          if (!country)
            throw new Error('Country not found')

          country.states = (country.states || []).filter(item => String(item.id) !== String(stateId))
          this.data = { data: [...list] }

          return
        }

        await countryService.removeState(countryId, stateId)
      }, 'stateErrors', 'Unable to process states.')
    },
  },
})
