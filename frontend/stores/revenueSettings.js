import { defineStore } from 'pinia'
import { MOCK_PROPERTIES } from '@/mocks/properties'
import { MOCK_REVENUE_SETTINGS } from '@/mocks/revenueSettings'
import { revenueSettingService } from '@/services/revenueSettingService'
import { mockAwareLoad, mockCreate, mockUpdate, normalizeList, runStoreAction } from '@/stores/mockHelpers'

const resolveProperty = propertyId => {
  const property = MOCK_PROPERTIES.find(item => String(item.id) === String(propertyId))
  if (!property)
    return { id: propertyId, property_title: `Property #${propertyId}`, property_image_url: null }

  return {
    id: property.id,
    property_title: property.property_title,
    property_image_url: property.property_image_url || null,
  }
}

const withPropertyMeta = payload => {
  const property = resolveProperty(payload.property_id)
  const ownerSettings = MOCK_PROPERTIES.find(item => String(item.id) === String(payload.property_id))?.revenue_settings

  return {
    ...payload,
    property,
    min_nightly_rate: payload.min_nightly_rate ?? ownerSettings?.min_nightly_rate ?? null,
    min_nights_per_booking: payload.min_nights_per_booking ?? ownerSettings?.min_nights_per_booking ?? null,
  }
}

export const useRevenueSettingsStore = defineStore('revenueSettings', {
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
        list: () => revenueSettingService.list(params),
        mocks: MOCK_REVENUE_SETTINGS,
        clone: true,
      }), 'errors', 'Unable to process revenue settings.')
    },
    async create(payload) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          return mockCreate(this, withPropertyMeta({
            id: Date.now(),
            ...payload,
            previous_base_rate: null,
            previous_min_rate: null,
          }))
        }

        const setting = (await revenueSettingService.create(payload)).data
        this.current = setting

        return setting
      }, 'errors', 'Unable to process revenue settings.')
    },
    async update(id, payload) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          const existing = normalizeList(this.data).find(row => String(row.id) === String(id))

          return mockUpdate(this, id, withPropertyMeta({
            ...payload,
            previous_base_rate: existing?.base_rate ?? existing?.previous_base_rate ?? null,
            previous_min_rate: existing?.min_rate ?? existing?.previous_min_rate ?? null,
          }))
        }

        const setting = (await revenueSettingService.update(id, payload)).data
        this.current = setting

        return setting
      }, 'errors', 'Unable to process revenue settings.')
    },
  },
})
