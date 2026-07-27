import { proxyRequest, getHeader, getRequestURL } from 'h3'

const laravelUrl = process.env.NUXT_LARAVEL_URL || 'http://localhost:8080'

// Proxy only POST /login to Laravel. GET /login must remain the Nuxt SPA page.
export default defineEventHandler(async (event) => {
  if (event.method !== 'POST') {
    return
  }

  if (getRequestURL(event).pathname !== '/login') {
    return
  }

  const accept = getHeader(event, 'accept') ?? ''

  // h3 strips `accept` from proxied headers by default, so Laravel treats the SPA
  // login as a browser form post (302 → followed → 401) and the session is lost.
  return proxyRequest(event, `${laravelUrl}/login`, {
    headers: {
      accept: accept || 'application/json',
    },
    fetchOptions: {
      redirect: 'manual',
    },
  })
})
