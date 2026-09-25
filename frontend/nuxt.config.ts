import svgLoader from 'vite-svg-loader'
import vuetify from 'vite-plugin-vuetify'
import { fileURLToPath } from 'node:url'
import { APP_BRAND_NAME } from './constants/brand.js'

const laravelUrl = process.env.NUXT_LARAVEL_URL || 'http://localhost:8080'
const proxyPaths = ['/sanctum', '/login', '/logout', '/admin']

/**
 * Browser document navigations must hit the Nuxt SPA. Only XHR/JSON
 * (Accept: application/json from services/http.js) proxies to Laravel.
 * Without this, GET /login and GET /admin/* loop: Nuxt -> Laravel -> FRONTEND_URL.
 * Nitro's built-in devProxy does not honor bypass reliably - see server/middleware/laravel-proxy.ts.
 */
function laravelViteProxy() {
  return {
    target: laravelUrl,
    changeOrigin: true,
    bypass(req: {
      headers: {
        accept?: string
        'sec-fetch-dest'?: string
        'sec-fetch-mode'?: string
        'x-requested-with'?: string
      }
      method?: string
      url?: string
    }) {
      const accept = req.headers.accept ?? ''
      const method = req.method ?? 'GET'
      const secFetchDest = req.headers['sec-fetch-dest'] ?? ''
      const secFetchMode = req.headers['sec-fetch-mode'] ?? ''
      const requestedWith = req.headers['x-requested-with'] ?? ''

      const isDocument = secFetchDest === 'document'
        || secFetchMode === 'navigate'
        || accept.includes('text/html')

      if (isDocument)
        return req.url

      if ((req.url === '/login' || req.url?.startsWith('/login?')) && method === 'GET')
        return req.url

      // Only SPA ofetch (X-Requested-With) reaches Laravel.
      if (requestedWith.toLowerCase() !== 'xmlhttprequest')
        return req.url
    },
  }
}

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  // ADR-018: Nuxt SPA - avoid SSR document requests colliding with Laravel proxy.
  ssr: false,

  runtimeConfig: {
    public: {
      apiBase: '',
      appName: process.env.NUXT_PUBLIC_APP_NAME || APP_BRAND_NAME,
    },
  },

  app: {
    head: {
      titleTemplate: `%s - ${APP_BRAND_NAME}`,
      title: APP_BRAND_NAME,

      link: [{
        rel: 'icon',
        type: 'image/x-icon',
        href: '/favicon.ico',
      }],
    },
  },

  devtools: {
    enabled: true,
  },

  css: [
    '@core/scss/template/index.scss',
    '@styles/styles.scss',
    '@/plugins/iconify/icons.css',
    '@layouts/styles/index.scss',
  ],

  components: {
    dirs: [{
      path: '@/@core/components',
      pathPrefix: false,
    }, {
      path: '~/components/global',
      global: true,
    }, {
      path: '~/components',
      pathPrefix: false,
    }],
  },

  plugins: ['@/plugins/vuetify/index.js', '@/plugins/iconify/index.js'],

  imports: {
    dirs: ['./@core/utils', './@core/composable/', './plugins/*/composables/*'],
  },

  hooks: {},

  experimental: {
    typedPages: true,
  },

  typescript: {
    tsConfig: {
      compilerOptions: {
        paths: {
          '@/*': ['../*'],
          '@layouts/*': ['../@layouts/*'],
          '@layouts': ['../@layouts'],
          '@core/*': ['../@core/*'],
          '@core': ['../@core'],
          '@images/*': ['../assets/images/*'],
          '@styles/*': ['../assets/styles/*'],
        },
      },
    },
  },

  // â„¹ï¸ Disable source maps until this is resolved: https://github.com/vuetifyjs/vuetify-loader/issues/290
  sourcemap: {
    server: false,
    client: false,
  },

  vue: {
    compilerOptions: {
      isCustomElement: tag => tag === 'swiper-container' || tag === 'swiper-slide',
    },
  },

  vite: {
    define: { 'process.env': {} },

    server: {
      proxy: Object.fromEntries(proxyPaths.map(path => [path, laravelViteProxy()])),
    },

    resolve: {
      alias: {
        '@': fileURLToPath(new URL('.', import.meta.url)),
        '@core': fileURLToPath(new URL('./@core', import.meta.url)),
        '@layouts': fileURLToPath(new URL('./@layouts', import.meta.url)),
        '@images': fileURLToPath(new URL('./assets/images/', import.meta.url)),
        '@styles': fileURLToPath(new URL('./assets/styles/', import.meta.url)),
        '@configured-variables': fileURLToPath(new URL('./assets/styles/variables/_template.scss', import.meta.url)),
      },
    },

    build: {
      chunkSizeWarningLimit: 5000,
    },

    optimizeDeps: {
      exclude: ['vuetify'],
      entries: [
        './**/*.vue',
      ],
    },

    plugins: [
      svgLoader(),
      vuetify({
        styles: {
          configFile: 'assets/styles/variables/_vuetify.scss',
        },
      }),
    ],
  },

  build: {
    transpile: ['vuetify'],
  },

  // Laravel JSON/API proxy: server/middleware/laravel-proxy.ts (devProxy bypass is unreliable)

  modules: ['@vueuse/nuxt', '@nuxtjs/device', '@pinia/nuxt', '@nuxtjs/tailwindcss'],
  compatibilityDate: '2026-07-26',
})