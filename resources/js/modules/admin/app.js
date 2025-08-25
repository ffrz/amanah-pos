import boot from "@/bootstrap";

import AuthenticatedLayout from "./layouts/AuthenticatedLayout.vue";
import GuestLayout from "./layouts/GuestLayout.vue";

boot({
  layouts: {
    authenticated: AuthenticatedLayout,
    guest: GuestLayout,
  },
  pagesGlob: import.meta.glob("./pages/**/*.vue", { eager: true }),
});
