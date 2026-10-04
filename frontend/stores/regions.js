import { defineStore } from 'pinia'
import { MOCK_REGIONS } from '@/mocks/regions'
import { regionService } from '@/services/regionService'
import { mockAwareLoad, mockCreate, mockRemove, mockUpdate, runStoreAction } from '@/stores/mockHelpers'

export const useRegionsStore = defineStore('regions', {
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
        list: () => regionService.list(params),
        mocks: MOCK_REGIONS,
      }), 'errors', 'Unable to process regions.')
    },
    async create(payload) {
      return runStoreAction(this, async () => {
        if (this.usingMocks)
          return mockCreate(this, { id: Date.now(), subregions: [], ...payload })

        const region = (await regionService.create(payload)).data
        this.current = region

        return region
      }, 'errors', 'Unable to process regions.')
    },
    async update(id, payload) {
      return runStoreAction(this, async () => {
        if (this.usingMocks)
          return mockUpdate(this, id, payload, { subregions: [] })

        const region = (await regionService.update(id, payload)).data
        this.current = region

        return region
      }, 'errors', 'Unable to process regions.')
    },
    async remove(id) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          mockRemove(this, id)

          return
        }

        await regionService.remove(id)
      }, 'errors', 'Unable to process regions.')
    },
  },
})
