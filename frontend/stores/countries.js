import { defineStore } from 'pinia'
import { MOCK_COUNTRIES } from '@/mocks/countries'
import { countryService } from '@/services/countryService'
import { mockAwareLoad, mockCreate, mockRemove, mockUpdate, normalizeList, runStoreAction } from '@/stores/mockHelpers'

const findCountry = (list, countryId) => list.find(item => String(item.id) === String(countryId))

const findState = (country, stateId) => (country?.states || []).find(item => String(item.id) === String(stateId))

export const useCountriesStore = defineStore('countries', {
  state: () => ({
    data: [],
    current: null,
    loading: false,
    errors: {},
    stateErrors: {},
    cityErrors: {},
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
          const country = findCountry(list, countryId)
          if (!country)
            throw new Error('Country not found')

          const state = { id: Date.now(), cities: [], ...payload }
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
          const country = findCountry(list, countryId)
          if (!country)
            throw new Error('Country not found')

          const states = country.states || []
          const index = states.findIndex(item => String(item.id) === String(stateId))
          const existing = index >= 0 ? states[index] : { id: stateId, cities: [] }
          const state = { ...existing, ...payload, id: existing.id, cities: existing.cities || [] }

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
          const country = findCountry(list, countryId)
          if (!country)
            throw new Error('Country not found')

          country.states = (country.states || []).filter(item => String(item.id) !== String(stateId))
          this.data = { data: [...list] }

          return
        }

        await countryService.removeState(countryId, stateId)
      }, 'stateErrors', 'Unable to process states.')
    },
    async createCity(countryId, stateId, payload) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          const list = normalizeList(this.data)
          const country = findCountry(list, countryId)
          const state = findState(country, stateId)
          if (!state)
            throw new Error('State not found')

          const city = { id: Date.now(), ...payload }
          state.cities = [...(state.cities || []), city]
          this.data = { data: [...list] }

          return city
        }

        return (await countryService.createCity(countryId, stateId, payload)).data
      }, 'cityErrors', 'Unable to process cities.')
    },
    async updateCity(countryId, stateId, cityId, payload) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          const list = normalizeList(this.data)
          const country = findCountry(list, countryId)
          const state = findState(country, stateId)
          if (!state)
            throw new Error('State not found')

          const cities = state.cities || []
          const index = cities.findIndex(item => String(item.id) === String(cityId))
          const existing = index >= 0 ? cities[index] : { id: cityId }
          const city = { ...existing, ...payload, id: existing.id }

          if (index >= 0)
            cities[index] = city
          else
            cities.push(city)

          state.cities = [...cities]
          this.data = { data: [...list] }

          return city
        }

        return (await countryService.updateCity(countryId, stateId, cityId, payload)).data
      }, 'cityErrors', 'Unable to process cities.')
    },
    async removeCity(countryId, stateId, cityId) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          const list = normalizeList(this.data)
          const country = findCountry(list, countryId)
          const state = findState(country, stateId)
          if (!state)
            throw new Error('State not found')

          state.cities = (state.cities || []).filter(item => String(item.id) !== String(cityId))
          this.data = { data: [...list] }

          return
        }

        await countryService.removeCity(countryId, stateId, cityId)
      }, 'cityErrors', 'Unable to process cities.')
    },
  },
})
