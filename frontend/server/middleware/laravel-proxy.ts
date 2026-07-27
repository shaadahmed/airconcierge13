import { proxyRequest, getHeader, getRequestURL } from 'h3'

const laravelUrl = process.env.NUXT_LARAVEL_URL || 'http://localhost:8080'

const proxyPrefixes = ['/sanctum', '/login', '/logout', '/admin']

/**
 * Proxy XHR/JSON to Laravel. Leave browser document navigations to the Nuxt SPA
 * so GET /login and GET /admin/* pages are not redirected in a loop.
 */
export default defineEventHandler(async event => {
  const path = event.path || ''
  const matches = proxyPrefixes.some(prefix => path === prefix || path.startsWith(`${prefix}/`) || path.startsWith(`${prefix}?`))

  if (!matches)
    return

  const accept = getHeader(event, 'accept') ?? ''
  const method = event.method || 'GET'

  if (accept.includes('text/html'))
    return

  // SPA owns the login page; Laravel only handles POST /login (and JSON).
  if ((path === '/login' || path.startsWith('/login?')) && method === 'GET')
    return

  const target = new URL(path, laravelUrl)
  const incoming = getRequestURL(event)

  target.search = incoming.search

  return proxyRequest(event, target.toString())
})
