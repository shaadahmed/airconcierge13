import { defineStore } from 'pinia'
import { MOCK_OWNERS } from '@/mocks/owners'
import { ownerService } from '@/services/ownerService'
import { mockAwareLoad, mockCreate, mockRemove, mockUpdate, runStoreAction } from '@/stores/mockHelpers'

export const useOwnersStore = defineStore('owners', {
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
        list: () => ownerService.list(params),
        mocks: MOCK_OWNERS,
      }), 'errors', 'Unable to process owners.')
    },
    async create(payload) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          return mockCreate(this, {
            id: Date.now(),
            ...payload,
            region: payload.region || (payload.region_id
              ? { id: payload.region_id, region_name: `Region #${payload.region_id}` }
              : null),
          })
        }

        const owner = (await ownerService.create(payload)).data
        this.current = owner

        return owner
      }, 'errors', 'Unable to process owners.')
    },
    async update(id, payload) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          const next = { ...payload }
          if (payload.region || payload.region_id) {
            next.region = payload.region || {
              id: payload.region_id,
              region_name: `Region #${payload.region_id}`,
            }
          }

          return mockUpdate(this, id, next)
        }

        const owner = (await ownerService.update(id, payload)).data
        this.current = owner

        return owner
      }, 'errors', 'Unable to process owners.')
    },
    async remove(id) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          mockRemove(this, id)

          return
        }

        await ownerService.remove(id)
      }, 'errors', 'Unable to process owners.')
    },
  },
})
