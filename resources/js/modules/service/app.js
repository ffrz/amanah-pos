import boot from "@/bootstrap";

import AuthenticatedLayout from "./layouts/AuthenticatedLayout.vue";
import GuestLayout from "../admin/layouts/GuestLayout.vue";

import { registerSW } from "virtual:pwa-register";

registerSW({
  scope: "/service/",
  onRegistered(r) {
    console.log("Service Service Worker registered:", r);
  },
  onError(error) {
    console.error("Service SW registration failed:", error);
  },
});

boot({
  layouts: {
    authenticated: AuthenticatedLayout,
    guest: GuestLayout,
  },
  pagesGlob: import.meta.glob("./pages/**/*.vue", { eager: true }),
});
