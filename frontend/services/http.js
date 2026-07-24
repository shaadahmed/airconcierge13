import { ofetch } from 'ofetch'

const getCookie = name => {
  if (!import.meta.client)
    return null

  return document.cookie
    .split('; ')
    .find(cookie => cookie.startsWith(`${name}=`))
    ?.split('=')
    .slice(1)
    .join('=')
}

const client = () => {
  const config = useRuntimeConfig()

  return ofetch.create({
    baseURL: config.public.apiBase,
    credentials: 'include',
    headers: {
      Accept: 'application/json',
    },
    onRequest({ options }) {
      const token = getCookie('XSRF-TOKEN')

      options.headers = new Headers(options.headers)
      options.headers.set('X-Requested-With', 'XMLHttpRequest')

      if (token)
        options.headers.set('X-XSRF-TOKEN', decodeURIComponent(token))
    },
  })
}

export const api = {
  get: (url, options = {}) => client()(url, { ...options, method: 'GET' }),
  post: (url, body, options = {}) => client()(url, { ...options, method: 'POST', body }),
  put: (url, body, options = {}) => client()(url, { ...options, method: 'PUT', body }),
  delete: (url, options = {}) => client()(url, { ...options, method: 'DELETE' }),
}
