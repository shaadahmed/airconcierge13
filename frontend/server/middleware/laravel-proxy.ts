import { proxyRequest, getHeader, getRequestURL } from 'h3'

const laravelUrl = process.env.NUXT_LARAVEL_URL || 'http://localhost:8080'

const proxyPrefixes = ['/sanctum', '/login', '/logout', '/admin']

function isDocumentNavigation(accept: string, secFetchDest: string, secFetchMode: string): boolean {
  return secFetchDest === 'document'
    || secFetchMode === 'navigate'
    || accept.includes('text/html')
}

/**
 * SPA http.js always sets X-Requested-With. Require that signal so bare
 * Accept: application/json (or browser quirks) cannot hard-refresh into Laravel JSON.
 */
function isApiRequest(requestedWith: string): boolean {
  return requestedWith.toLowerCase() === 'xmlhttprequest'
}

/**
 * Proxy XHR/JSON to Laravel. Leave browser document navigations to the Nuxt SPA
 * so GET /login and GET /admin/* hard refreshes are not forwarded as API calls.
 */
export default defineEventHandler(async event => {
  const path = event.path || ''
  const matches = proxyPrefixes.some(prefix => path === prefix || path.startsWith(`${prefix}/`) || path.startsWith(`${prefix}?`))

  if (!matches)
    return

  const accept = getHeader(event, 'accept') ?? ''
  const method = event.method || 'GET'
  const secFetchDest = getHeader(event, 'sec-fetch-dest') ?? ''
  const secFetchMode = getHeader(event, 'sec-fetch-mode') ?? ''
  const requestedWith = getHeader(event, 'x-requested-with') ?? ''

  if (isDocumentNavigation(accept, secFetchDest, secFetchMode))
    return

  // SPA owns the login page; Laravel only handles POST /login (and JSON XHR).
  if ((path === '/login' || path.startsWith('/login?')) && method === 'GET')
    return

  if (!isApiRequest(requestedWith))
    return

  const target = new URL(path, laravelUrl)
  const incoming = getRequestURL(event)

  target.search = incoming.search

  // h3 strips `accept` from proxied headers by default, which makes Laravel
  // treat SPA XHR as a browser form post (redirect → followed → 401, session lost).
  // Re-attach Accept and never follow redirects so Set-Cookie from login survives.
  return proxyRequest(event, target.toString(), {
    headers: {
      accept: accept || 'application/json',
    },
    fetchOptions: {
      redirect: 'manual',
    },
  })
})
