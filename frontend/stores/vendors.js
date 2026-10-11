import { defineStore } from 'pinia'
import { MOCK_VENDORS } from '@/mocks/vendors'
import { vendorService } from '@/services/vendorService'
import { mockAwareLoad, mockCreate, mockRemove, mockUpdate, runStoreAction } from '@/stores/mockHelpers'

export const useVendorsStore = defineStore('vendors', {
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
        list: () => vendorService.list(params),
        mocks: MOCK_VENDORS,
      }), 'errors', 'Unable to process vendors.')
    },
    async create(payload) {
      return runStoreAction(this, async () => {
        if (this.usingMocks)
          return mockCreate(this, { id: Date.now(), ...payload })

        const vendor = (await vendorService.create(payload)).data
        this.current = vendor

        return vendor
      }, 'errors', 'Unable to process vendors.')
    },
    async update(id, payload) {
      return runStoreAction(this, async () => {
        if (this.usingMocks)
          return mockUpdate(this, id, payload)

        const vendor = (await vendorService.update(id, payload)).data
        this.current = vendor

        return vendor
      }, 'errors', 'Unable to process vendors.')
    },
    async remove(id) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          mockRemove(this, id)

          return
        }

        await vendorService.remove(id)
      }, 'errors', 'Unable to process vendors.')
    },
  },
})
