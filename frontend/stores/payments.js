import { defineStore } from 'pinia'
import {
  findMockBookingPayment,
  findMockPropertyPayment,
  MOCK_BOOKING_PAYMENTS,
  MOCK_PROPERTY_PAYMENTS,
} from '@/mocks/payments'
import { paymentService } from '@/services/paymentService'
import { mockCreate, mockRemove, mockUpdate, normalizeList, runStoreAction } from '@/stores/mockHelpers'

const loadListOrMocks = async ({ list, assign, mocks }) => {
  try {
    const response = await list()
    const rows = normalizeList(response.data)

    if (rows.length) {
      assign(response.data)

      return false
    }
  }
  catch {
    // Fall through to UI preview fixtures.
  }

  assign({ data: structuredClone(mocks) })

  return true
}

export const usePaymentsStore = defineStore('payments', {
  state: () => ({
    data: [],
    propertyPayments: [],
    current: null,
    loading: false,
    errors: {},
    usingMocks: false,
  }),
  actions: {
    async load(params = {}) {
      await this.loadAll(params)
    },
    async loadPropertyPayments(params = {}) {
      await this.loadAll(params)
    },
    async loadAll(params = {}) {
      await runStoreAction(this, async () => {
        const bookingUsedMocks = await loadListOrMocks({
          list: () => paymentService.list(params),
          assign: rows => { this.data = rows },
          mocks: MOCK_BOOKING_PAYMENTS,
        })

        const propertyUsedMocks = await loadListOrMocks({
          list: () => paymentService.listPropertyPayments(params),
          assign: rows => { this.propertyPayments = rows },
          mocks: MOCK_PROPERTY_PAYMENTS,
        })

        this.usingMocks = bookingUsedMocks || propertyUsedMocks
      }, 'errors', 'Unable to load payments.')
    },
    async loadOne(kind, id) {
      await runStoreAction(this, async () => {
        const isProperty = kind === 'property'
        const list = normalizeList(isProperty ? this.propertyPayments : this.data)
        const listHit = list.find(item => String(item.id) === String(id))

        if (listHit) {
          this.current = listHit

          return listHit
        }

        try {
          if (!isProperty) {
            const response = await paymentService.list({ id })
            const rows = normalizeList(response.data)
            const hit = rows.find(item => String(item.id) === String(id))
            if (hit) {
              this.current = hit
              this.usingMocks = false

              return hit
            }
          }
        }
        catch {
          // Fall through to mocks.
        }

        const mock = isProperty ? findMockPropertyPayment(id) : findMockBookingPayment(id)
        if (!mock) {
          this.errors = { general: ['Payment not found.'] }
          this.current = null
          throw new Error('Payment not found')
        }

        this.current = mock
        this.usingMocks = true

        return mock
      }, 'errors', 'Unable to load payment.')
    },
    async create(data) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          return mockCreate(this, {
            id: Date.now(),
            payment_status: 'Paid',
            deleted_at: null,
            latest_action: `CREATED · ${new Date().toLocaleDateString()}`,
            ...data,
          })
        }

        this.current = (await paymentService.create(data)).data

        return this.current
      }, 'errors', 'Unable to create payment.')
    },
    async createPropertyPayment(data) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          const row = {
            id: Date.now(),
            payment_status: 'Paid',
            deleted_at: null,
            latest_action: `CREATED · ${new Date().toLocaleDateString()}`,
            ...data,
          }

          const list = normalizeList(this.propertyPayments)

          this.propertyPayments = { data: [row, ...list] }
          this.current = row

          return row
        }

        this.current = (await paymentService.createPropertyPayment(data)).data

        return this.current
      }, 'errors', 'Unable to create property payment.')
    },
    async save(kind, id, data) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          if (kind === 'property') {
            const list = normalizeList(this.propertyPayments)
            const index = list.findIndex(item => String(item.id) === String(id))
            const existing = index >= 0 ? list[index] : { id }
            const row = { ...existing, ...data, id: existing.id }

            if (index >= 0)
              list[index] = row
            else
              list.unshift(row)

            this.propertyPayments = { data: list }
            this.current = row

            return row
          }

          return mockUpdate(this, id, data)
        }

        if (kind === 'property') {
          this.current = (await paymentService.createPropertyPayment({ id, ...data })).data

          return this.current
        }

        this.current = (await paymentService.create({ id, ...data })).data

        return this.current
      }, 'errors', 'Unable to save payment.')
    },
    async remove(id) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          mockRemove(this, id)
          if (String(this.current?.id) === String(id))
            this.current = null

          return
        }

        await paymentService.remove(id)
      }, 'errors', 'Unable to delete payment.')
    },
    async removePropertyPayment(id) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          this.propertyPayments = {
            data: normalizeList(this.propertyPayments).filter(item => String(item.id) !== String(id)),
          }
          if (String(this.current?.id) === String(id))
            this.current = null

          return
        }

        await paymentService.removePropertyPayment(id)
      }, 'errors', 'Unable to delete property payment.')
    },
  },
})
