import { defineStore } from 'pinia'
import { MOCK_USERS } from '@/mocks/users'
import { userService } from '@/services/userService'
import { mockAwareLoad, mockCreate, mockRemove, mockUpdate, runStoreAction } from '@/stores/mockHelpers'

const toApiPayload = payload => {
  const firstname = payload.firstname || ''
  const lastname = payload.lastname || ''
  const name = payload.name || [firstname, lastname].filter(Boolean).join(' ').trim()

  const body = {
    name: name || null,
    email: payload.email || null,
    role: payload.role || null,
    active: Number(payload.active) === 1 || payload.active === true,
  }

  if (payload.password)
    body.password = payload.password

  return body
}

const normalizeUser = user => {
  if (!user || typeof user !== 'object')
    return user

  const role = user.role?.value || user.role || ''
  let firstname = user.firstname || ''
  let lastname = user.lastname || ''

  if (!firstname && !lastname && user.name) {
    const parts = String(user.name).trim().split(/\s+/)
    firstname = parts[0] || ''
    lastname = parts.slice(1).join(' ')
  }

  return {
    ...user,
    firstname,
    lastname,
    username: user.username || user.email || '',
    role,
    role_label: user.role_label || role,
    active: user.active === false || user.active === 0 || user.active === '0' ? 0 : 1,
    owners: Array.isArray(user.owners) ? user.owners : [],
    managers: Array.isArray(user.managers) ? user.managers : [],
  }
}

export const useUsersStore = defineStore('users', {
  state: () => ({
    data: [],
    current: null,
    loading: false,
    errors: {},
    usingMocks: false,
  }),
  actions: {
    async load(params = {}) {
      await runStoreAction(this, async () => {
        await mockAwareLoad(this, {
          list: () => userService.list(params),
          mocks: MOCK_USERS,
          clone: true,
        })

        const list = Array.isArray(this.data?.data)
          ? this.data.data
          : (Array.isArray(this.data) ? this.data : [])

        this.data = { data: list.map(normalizeUser) }
      }, 'errors', 'Unable to process users.')
    },
    async create(payload) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          return mockCreate(this, {
            id: Date.now(),
            name: [payload.firstname, payload.lastname].filter(Boolean).join(' ').trim(),
            role_label: payload.role_label || payload.role,
            last_login: null,
            ...payload,
          })
        }

        const user = (await userService.create(toApiPayload(payload))).data
        this.current = user

        return user
      }, 'errors', 'Unable to process users.')
    },
    async update(id, payload) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          return mockUpdate(this, id, {
            ...payload,
            name: [payload.firstname, payload.lastname].filter(Boolean).join(' ').trim(),
            role_label: payload.role_label || payload.role,
          })
        }

        const user = (await userService.update(id, toApiPayload(payload))).data
        this.current = user

        return user
      }, 'errors', 'Unable to process users.')
    },
    async remove(id) {
      return runStoreAction(this, async () => {
        if (this.usingMocks) {
          mockRemove(this, id)

          return
        }

        await userService.remove(id)
      }, 'errors', 'Unable to process users.')
    },
  },
})
