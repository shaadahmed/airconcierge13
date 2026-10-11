import { defineStore } from 'pinia'
import { MOCK_MANAGERS } from '@/mocks/managers'
import { managerService } from '@/services/managerService'
import { mockAwareLoad, mockCreate, mockRemove, mockUpdate, runStoreAction } from '@/stores/mockHelpers'

export const useManagersStore = defineStore('managers', {
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
        list: () => managerService.list(params),
        mocks: MOCK_MANAGERS,
      }), 'errors', 'Unable to process managers.')
    },
    async create(payload) {
      return runStoreAction(this, async () => {
        if (this.usingMocks)
          return mockCreate(this, { id: Date.now(), ...payload })

        const manager = (await managerService.create(payload)).data
        this.current = manager

        return manager
      }, 'errors', 'Unable to process managers.')
    },
    async update(id, payload) {
      return runStoreAction(this, async () => {
        if (this.usingMocks)
          return mockUpdate(this, id, payload)

        const manager = (await managerService.update(id, payload)).data
        this.current = manager

        return manager
      }, 'errors', 'Unable to process managers.')
    },
    async remove(id) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          mockRemove(this, id)

          return
        }

        await managerService.remove(id)
      }, 'errors', 'Unable to process managers.')
    },
  },
})
