import { defineStore } from 'pinia'
import { findMockOwnerBlock, MOCK_OWNER_BLOCKS } from '@/mocks/ownerBlocks'
import { nightsBetween } from '@/constants/ownerBlocks'
import { ownerBlockService } from '@/services/ownerBlockService'
import { mockAwareLoad, mockCreate, mockRemove, mockUpdate, normalizeList, runStoreAction } from '@/stores/mockHelpers'

export const useOwnerBlocksStore = defineStore('ownerBlocks', {
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
        list: () => ownerBlockService.list(params),
        mocks: MOCK_OWNER_BLOCKS,
        clone: true,
      }), 'errors', 'Unable to load owner blocks.')
    },
    async loadOne(id) {
      await runStoreAction(this, async () => {
        try {
          const response = await ownerBlockService.get(id)
          const block = response.data?.data || response.data

          if (block?.id) {
            this.current = block
            this.usingMocks = false

            return block
          }
        }
        catch {
          // Fall through to UI preview fixtures.
        }

        const listHit = normalizeList(this.data).find(item => String(item.id) === String(id))
        const mock = listHit || findMockOwnerBlock(id)
        if (!mock) {
          this.errors = { general: ['Owner block not found.'] }
          this.current = null
          throw new Error('Owner block not found')
        }

        this.current = mock
        this.usingMocks = true

        return mock
      }, 'errors', 'Unable to load owner blocks.')
    },
    async create(data) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          const nights = nightsBetween(data.reservation_start_date, data.reservation_end_date)
          const start = data.reservation_start_date ? new Date(data.reservation_start_date) : null

          return mockCreate(this, {
            id: Date.now(),
            no_of_nights: nights,
            payment_of_cleaners: data.cleaning_post_order_stay === '1' ? 185 : 0,
            exit_cleaning: data.cleaning_post_order_stay,
            month: start ? start.getMonth() + 1 : null,
            year: start ? start.getFullYear() : null,
            created_at: new Date().toISOString(),
            latest_log: 'Created (UI preview)',
            deleted_at: null,
            property: data.property || {
              id: data.property_id,
              property_title: data.property_title || `Property #${data.property_id}`,
              street_address: data.street_address || '',
              property_image_url: null,
            },
            property_title: data.property_title,
            street_address: data.street_address || '',
            location: data.location || '—',
            region_id: data.region_id ?? null,
            subregion_id: data.subregion_id ?? null,
            ...data,
          })
        }

        this.current = (await ownerBlockService.create(data)).data

        return this.current
      }, 'errors', 'Unable to create owner block.')
    },
    async save(id, data) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          const nights = nightsBetween(data.reservation_start_date, data.reservation_end_date)
          const start = data.reservation_start_date ? new Date(data.reservation_start_date) : null

          return mockUpdate(this, id, {
            ...data,
            no_of_nights: nights,
            exit_cleaning: data.cleaning_post_order_stay,
            payment_of_cleaners: data.cleaning_post_order_stay === '1'
              ? (Number(data.payment_of_cleaners) || 185)
              : 0,
            month: start ? start.getMonth() + 1 : null,
            year: start ? start.getFullYear() : null,
            latest_log: 'Updated (UI preview)',
          })
        }

        this.current = (await ownerBlockService.update(id, data)).data

        return this.current
      }, 'errors', 'Unable to save owner block.')
    },
    async remove(id) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          mockRemove(this, id)
          if (String(this.current?.id) === String(id))
            this.current = null

          return
        }

        await ownerBlockService.remove(id)
      }, 'errors', 'Unable to delete owner block.')
    },
  },
})
