import { defineStore } from 'pinia'
import { MOCK_OWNER_AGREEMENT } from '@/mocks/cms'
import { cmsService } from '@/services/cmsService'
import { runStoreAction } from '@/stores/mockHelpers'

export const useCmsStore = defineStore('cms', {
  state: () => ({
    data: null,
    loading: false,
    errors: {},
    usingMocks: false,
  }),
  actions: {
    async load(pageId = 'owner-agreement') {
      await runStoreAction(this, async () => {
        try {
          const response = await cmsService.show(pageId)
          const payload = response.data?.data || response.data

          if (payload?.content != null || payload?.page_id) {
            this.data = {
              page_id: payload.page_id || pageId,
              content: payload.content ?? '',
              updated_at: payload.updated_at ?? null,
            }
            this.usingMocks = false

            return
          }
        }
        catch {
          // Fall through to UI preview fixtures.
        }

        this.data = structuredClone(MOCK_OWNER_AGREEMENT)
        this.usingMocks = true
      }, 'errors', 'Unable to load CMS content.')
    },
    async save(pageId, content) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          this.data = {
            page_id: pageId,
            content,
            updated_at: new Date().toISOString(),
          }

          return this.data
        }

        const response = await cmsService.update(pageId, { content })
        const payload = response.data?.data || response.data || {}

        this.data = {
          page_id: payload.page_id || pageId,
          content: payload.content ?? content,
          updated_at: payload.updated_at ?? new Date().toISOString(),
        }

        return this.data
      }, 'errors', 'Unable to save CMS content.')
    },
  },
})
