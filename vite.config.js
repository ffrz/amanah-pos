import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import { quasar, transformAssetUrls } from "@quasar/vite-plugin";
import { VitePWA } from 'vite-plugin-pwa';


// Konfigurasi aset PWA dasar yang dibagi antara kedua mode
const basePWAConfig = {
  registerType: 'autoUpdate',
  outDir: 'public/build',
  // Workbox (Service Worker) akan meng-cache aset yang sama
  workbox: {
    maximumFileSizeToCacheInBytes: 5242880,
    globPatterns: ['**/*.{js,css,html,ico,png,svg,vue,ttf,woff2}'],
    navigateFallbackDenylist: [/^\/api/],
  },
  devOptions: {
    enabled: true
  },
  // Ikon PWA (pastikan file ini ada di folder public/)
  icons: [
    { src: 'pwa-192x192.png', sizes: '192x192', type: 'image/png' },
    { src: 'pwa-512x512.png', sizes: '512x512', type: 'image/png' },
    { src: 'pwa-512x512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
  ],
  background_color: '#ffffff',
  display: 'standalone',
  scope: '/',
};


// 1. Konfigurasi Khusus Customer (Warna Biru Quasar)
const customerPWA = VitePWA({
  ...basePWAConfig,
  filename: 'customer-manifest.webmanifest', // Nama file unik
  manifest: {
    name: 'Aplikasi Pelanggan Amanah',
    short_name: 'Customer App',
    description: 'Aplikasi untuk pelanggan dan pengguna umum.',
    theme_color: '#1976d2', // Biru
    start_url: '/', // Mulai dari halaman utama
  },
});

// 2. Konfigurasi Khusus Admin (Warna Merah/Aksen)
const adminPWA = VitePWA({
  ...basePWAConfig,
  filename: 'admin-manifest.webmanifest', // Nama file unik
  manifest: {
    name: 'Aplikasi Admin & Manajemen',
    short_name: 'Admin Panel',
    description: 'Panel untuk manajemen backend dan admin.',
    theme_color: '#ff5252', // Merah (membedakan dari Customer)
    start_url: '/admin', // Mulai dari rute admin
  },
});

export default defineConfig({
  build: {
    rollupOptions: {
      output: {
        // manualChunks: {
        //   vendor1: ['vue', 'quasar', 'dayjs', 'material-design-icons-iconfont', 'vue-i18n'],
        //   vendor2: ['vue-echarts'],
        //   vendor3: ['echarts'],
        //   // components: [
        //   //   '/resources/js/pages/admin/auth/Login.vue',
        //   //   '/resources/js/pages/admin/auth/Register.vue',
        //   // ],
        // },
      },
    },
  },
  plugins: [
    vue({
      template: { transformAssetUrls },
    }),
    // @quasar/plugin-vite options list:
    // https://github.com/quasarframework/quasar/blob/dev/vite-plugin/index.d.ts
    quasar({
      sassVariables: "/resources/css/quasar-variables.sass",
    }),
    laravel({
      input: [
        "resources/css/app.css",
        // "resources/js/app.js",
        "resources/js/modules/admin/app.js",
        "resources/js/modules/customer/app.js",
      ],
      refresh: true,
    }),
    customerPWA, // Generate Manifest Customer
    adminPWA,    // Generate Manifest Admin
  ],
  optimizeDeps: {
    exclude: ['qs', 'qs-esm'],
  },
});
