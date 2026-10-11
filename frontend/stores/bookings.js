import { defineStore } from 'pinia'
import { findMockBooking, MOCK_BOOKINGS } from '@/mocks/bookings'
import { bookingService } from '@/services/bookingService'
import { mockAwareLoad, mockCreate, mockRemove, mockUpdate, normalizeList, runStoreAction } from '@/stores/mockHelpers'

export const useBookingsStore = defineStore('bookings', {
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
        list: () => bookingService.list(params),
        mocks: MOCK_BOOKINGS,
        clone: true,
      }), 'errors', 'Unable to load bookings.')
    },
    async loadOne(id) {
      await runStoreAction(this, async () => {
        try {
          const response = await bookingService.get(id)
          const booking = response.data?.data || response.data

          if (booking?.id) {
            this.current = booking
            this.usingMocks = false

            return booking
          }
        }
        catch {
          // Fall through to UI preview fixtures.
        }

        const listHit = normalizeList(this.data).find(item => String(item.id) === String(id))
        const mock = listHit || findMockBooking(id)
        if (!mock) {
          this.errors = { general: ['Booking not found.'] }
          this.current = null
          throw new Error('Booking not found')
        }

        this.current = mock
        this.usingMocks = true

        return mock
      }, 'errors', 'Unable to load bookings.')
    },
    async create(data) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          return mockCreate(this, {
            id: Date.now(),
            status: 'pending',
            cancelled_booking: false,
            nightly_rate: data.nightly_rate ?? null,
            location: data.location || '—',
            property: data.property || {
              id: data.property_id,
              property_title: data.property_title || `Property #${data.property_id}`,
              property_image_url: null,
            },
            ...data,
          })
        }

        this.current = (await bookingService.create(data)).data

        return this.current
      }, 'errors', 'Unable to create booking.')
    },
    async save(id, data) {
      return runStoreAction(this, async () => {
        if (this.usingMocks)
          return mockUpdate(this, id, data)

        this.current = (await bookingService.update(id, data)).data

        return this.current
      }, 'errors', 'Unable to save booking.')
    },
    async remove(id) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          mockRemove(this, id)
          if (String(this.current?.id) === String(id))
            this.current = null

          return
        }

        await bookingService.remove(id)
      }, 'errors', 'Unable to delete booking.')
    },
    async cancel(id) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          return mockUpdate(this, id, {
            cancelled_booking: true,
            status: 'cancelled',
          })
        }

        this.current = (await bookingService.cancel(id)).data

        return this.current
      }, 'errors', 'Unable to cancel booking.')
    },
  },
})
