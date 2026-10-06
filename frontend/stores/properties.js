import { defineStore } from 'pinia'
import { findCityById } from '@/mocks/countries'
import { findMockProperty, MOCK_PROPERTIES } from '@/mocks/properties'
import { propertyService } from '@/services/propertyService'
import { mockAwareLoad, mockCreate, mockRemove, mockUpdate, normalizeList, runStoreAction } from '@/stores/mockHelpers'

const withResolvedCity = payload => {
  const cityId = payload?.city_id
  if (cityId == null || cityId === '')
    return { ...payload, city_id: null, city: null }

  const city = findCityById(cityId)

  return {
    ...payload,
    city_id: cityId,
    city: city || payload.city || null,
  }
}

export const usePropertiesStore = defineStore('properties', {
  state: () => ({
    data: [],
    current: null,
    loading: false,
    errors: {},
    usingMocks: false,
  }),
  actions: {
    async load(params = {}) {
      await runStoreAction(this, () => mockAwareLoad(this, {
        list: () => propertyService.list(params),
        mocks: MOCK_PROPERTIES,
      }), 'errors', 'Unable to process properties.')
    },
    async loadOne(id) {
      await runStoreAction(this, async () => {
        try {
          const response = await propertyService.show(id)
          const property = response.data?.data || response.data

          if (property?.id) {
            this.current = property
            this.usingMocks = false

            return property
          }
        }
        catch {
          // Fall through to UI preview fixtures.
        }

        const mock = findMockProperty(id)
        if (!mock) {
          this.errors = { general: ['Property not found.'] }
          this.current = null
          throw new Error('Property not found')
        }

        this.current = mock
        this.usingMocks = true

        return mock
      }, 'errors', 'Unable to process properties.')
    },
    async create(payload) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          return mockCreate(this, {
            id: Date.now(),
            ...withResolvedCity(payload),
            email_title: payload.email_titles?.[0] || payload.email_title || null,
            owners: (payload.owners || []).map(ownerId => ({ id: ownerId })),
            status: payload.status ?? 1,
          })
        }

        const property = (await propertyService.create(payload)).data
        this.current = property

        return property
      }, 'errors', 'Unable to process properties.')
    },
    async update(id, payload) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          const existing = normalizeList(this.data).find(item => String(item.id) === String(id))
            || findMockProperty(id)
            || { id }

          return mockUpdate(this, id, {
            ...withResolvedCity(payload),
            email_title: payload.email_titles?.[0] || payload.email_title || existing.email_title || null,
          })
        }

        const property = (await propertyService.update(id, payload)).data
        this.current = property

        return property
      }, 'errors', 'Unable to process properties.')
    },
    async remove(id) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          mockRemove(this, id)
          if (String(this.current?.id) === String(id))
            this.current = null

          return
        }

        await propertyService.remove(id)
      }, 'errors', 'Unable to process properties.')
    },
  },
})
