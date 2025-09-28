import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import { quasar, transformAssetUrls } from "@quasar/vite-plugin";
import { VitePWA } from 'vite-plugin-pwa';
import { resolve } from 'path'
import { copyFileSync, mkdirSync, existsSync } from 'fs'

export default defineConfig({
  plugins: [
    vue({
      template: { transformAssetUrls },
    }),
    quasar({
      sassVariables: "/resources/css/quasar-variables.sass",
    }),
    laravel({
      input: [
        "resources/css/app.css",
        "resources/js/modules/admin/app.js",
        "resources/js/modules/customer/app.js",
      ],
      refresh: true,
    }),

    // ----------------------------------------------------
    // PWA Configuration: Customer Only
    // ----------------------------------------------------
    VitePWA({
      manifestFilename: 'customer-manifest.webmanifest',
      filename: 'sw-customer.js',
      registerType: 'autoUpdate',
      injectRegister: 'auto', // biar otomatis include registerSW, aman
      manifest: {
        name: 'Amanah Customer',
        short_name: 'AmanahCustomer',
        description: 'Amanah POS untuk pelanggan.',
        theme_color: '#4f46e5',
        background_color: '#ffffff',
        icons: [
          { src: 'pwa-192x192.png', sizes: '192x192', type: 'image/png' },
          { src: 'pwa-512x512.png', sizes: '512x512', type: 'image/png' },
          { src: 'pwa-512x512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
        ],
        start_url: '/customer',
        scope: '/customer/',
        display: 'standalone',
      },
      workbox: {
        navigateFallback: '/customer',
        maximumFileSizeToCacheInBytes: 5242880,
        globPatterns: [
          '**/*.{js,css,html}',
          'pwa-*.png',
        ],
      },
    }),
    VitePWA({
      manifestFilename: 'admin-manifest.webmanifest',
      filename: 'sw-admin.js',
      registerType: 'autoUpdate',
      injectRegister: 'auto', // biar otomatis include registerSW, aman
      manifest: {
        name: 'Amanah Admin',
        short_name: 'AmanahAdmin',
        description: 'Amanah POS untuk staff.',
        theme_color: '#4f46e5',
        background_color: '#ffffff',
        icons: [
          { src: 'pwa-192x192.png', sizes: '192x192', type: 'image/png' },
          { src: 'pwa-512x512.png', sizes: '512x512', type: 'image/png' },
          { src: 'pwa-512x512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
        ],
        start_url: '/admin',
        scope: '/admin/',
        display: 'standalone',
      },
      workbox: {
        maximumFileSizeToCacheInBytes: 5242880,
        navigateFallback: '/customer',
        globPatterns: [
          '**/*.{js,css,html}',
          'pwa-*.png',
        ],
      },
    }),
    {
      name: 'copy-pwa-icons',
      closeBundle() {
        const outDir = 'public/build'
        if (!existsSync(outDir)) {
          mkdirSync(outDir, { recursive: true })
        }

        const files = [
          'pwa-192x192.png',
          'pwa-512x512.png'
        ]

        for (const f of files) {
          const dest = resolve(outDir, f.split('/').pop())
          copyFileSync(resolve('public', f), dest)
          console.log(`✅ Copied ${f} -> ${dest}`)
        }
      }
    }
  ],
  optimizeDeps: {
    exclude: ['qs', 'qs-esm'],
  },
});
