import boot from "@/bootstrap";

import AuthenticatedLayout from "./layouts/AuthenticatedLayout.vue";
import GuestLayout from "./layouts/GuestLayout.vue";

// --- Tambahkan ini di bagian atas file customer/app.js Anda ---
import { registerSW } from 'virtual:pwa-register';

// Register Customer SW dengan scope root
const updateSW = registerSW({
  immediate: true,
  onNeedRefresh() {
    console.log('Update tersedia, refresh untuk mendapatkan versi terbaru.')
  },
  onOfflineReady() {
    console.log('Aplikasi siap digunakan offline.')
  },
})

// -------------------------------------------------------------

// ... kode inisialisasi Vue/Quasar/Inertia Anda untuk Customer ...


boot({
  layouts: {
    authenticated: AuthenticatedLayout,
    guest: GuestLayout,
  },
  pagesGlob: import.meta.glob("./pages/**/*.vue", { eager: true }),
});
