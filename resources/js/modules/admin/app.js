import boot from "@/bootstrap";

import AuthenticatedLayout from "./layouts/AuthenticatedLayout.vue";
import GuestLayout from "./layouts/GuestLayout.vue";

// --- Tambahkan ini di bagian atas file admin/app.js Anda ---
import { registerSW } from 'virtual:pwa-register';

// Register Admin SW dengan scope yang sesuai
registerSW({
  scope: '/admin/', // Scope yang ketat untuk Admin
  onRegistered(r) {
    console.log('Admin Service Worker registered:', r);
  },
  onError(error) {
    console.error('Admin SW registration failed:', error);
  },
});
// ----------------------------------------------------------

// ... kode inisialisasi Vue/Quasar/Inertia Anda untuk Admin ...

boot({
  layouts: {
    authenticated: AuthenticatedLayout,
    guest: GuestLayout,
  },
  pagesGlob: import.meta.glob("./pages/**/*.vue", { eager: true }),
});
