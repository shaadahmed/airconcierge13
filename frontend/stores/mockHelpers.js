/**
 * Shared helpers for Pinia stores that fall back to UI-preview mocks.
 */

export const normalizeList = payload => {
  const data = payload?.data || payload || []

  return Array.isArray(data) ? data : []
}

export const mockAwareLoad = async (store, { list, mocks, clone = false }) => {
  try {
    const response = await list()
    const rows = normalizeList(response.data)

    if (rows.length) {
      store.data = response.data
      store.usingMocks = false

      return
    }
  }
  catch {
    // Fall through to UI preview fixtures.
  }

  store.data = { data: clone ? structuredClone(mocks) : [...mocks] }
  store.usingMocks = true
}

export const mockCreate = (store, row) => {
  const list = normalizeList(store.data)
  store.data = { data: [row, ...list] }
  store.current = row

  return row
}

export const mockUpdate = (store, id, payload, defaults = {}) => {
  const list = normalizeList(store.data)
  const index = list.findIndex(item => String(item.id) === String(id))
  const existing = index >= 0 ? list[index] : { id, ...defaults }
  const row = { ...existing, ...payload, id: existing.id }

  if (index >= 0)
    list[index] = row
  else
    list.unshift(row)

  store.data = { data: list }
  store.current = row

  return row
}

export const mockRemove = (store, id) => {
  store.data = { data: normalizeList(store.data).filter(item => String(item.id) !== String(id)) }
}

export async function runStoreAction(store, callback, errorKey = 'errors', fallback = 'Unable to process request.') {
  store.loading = true
  store[errorKey] = {}

  try {
    return await callback()
  }
  catch (error) {
    store[errorKey] = error?.data?.errors || { general: [error?.data?.message || error?.message || fallback] }
    throw error
  }
  finally {
    store.loading = false
  }
}
