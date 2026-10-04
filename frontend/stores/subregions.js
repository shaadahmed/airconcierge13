import { defineStore } from 'pinia'
import { MOCK_SUBREGIONS } from '@/mocks/subregions'
import { subregionService } from '@/services/subregionService'
import { mockAwareLoad, mockCreate, mockRemove, mockUpdate, runStoreAction } from '@/stores/mockHelpers'

export const useSubregionsStore = defineStore('subregions', {
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
        list: () => subregionService.list(params),
        mocks: MOCK_SUBREGIONS,
      }), 'errors', 'Unable to process subregions.')
    },
    async create(payload) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          return mockCreate(this, {
            id: Date.now(),
            ...payload,
            region: payload.region || { id: payload.region_id, region_name: `Region #${payload.region_id}` },
          })
        }

        const row = (await subregionService.create(payload)).data
        this.current = row

        return row
      }, 'errors', 'Unable to process subregions.')
    },
    async update(id, payload) {
      return runStoreAction(this, async () => {
        if (this.usingMocks)
          return mockUpdate(this, id, payload)

        const row = (await subregionService.update(id, payload)).data
        this.current = row

        return row
      }, 'errors', 'Unable to process subregions.')
    },
    async remove(id) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          mockRemove(this, id)

          return
        }

        await subregionService.remove(id)
      }, 'errors', 'Unable to process subregions.')
    },
  },
})
