// src/plugins/PermissionPlugin.js

import { usePage } from '@inertiajs/vue3'; // Tetap butuh ini untuk mengakses props Inertia

/**
 * Vue Plugin untuk menyediakan metode otorisasi global ($can, $hasRole).
 * Logika pengecekan izin dan peran langsung di dalam plugin.
 */
export default {
  install: (app) => {
    // Dapatkan akses ke page props Inertia.
    // Catatan: usePage() harus dipanggil di dalam setup() atau install() plugin
    // karena ia bergantung pada konteks aplikasi Vue.
    const page = usePage();

    /**
     * Memeriksa apakah pengguna memiliki izin tertentu.
     * Admin otomatis memiliki semua izin.
     * @param {string} permissionName - Nama izin yang akan diperiksa.
     * @returns {boolean} True jika pengguna memiliki izin, false jika tidak.
     */
    app.config.globalProperties.$canAccess = (permissionName) => {
      const user = page.props.auth?.user;

      // Jika tidak ada user login, tidak ada izin
      if (!user) {
        return false;
      }

      const permissions = user.permissions || [];
      const userRoles = user.roles || [];

      // Admin Bypass: Jika user memiliki peran 'admin', selalu kembalikan true
      if (userRoles.includes('admin')) {
        return true;
      }

      // Periksa apakah izin yang diminta ada dalam array izin user
      return permissions.includes(permissionName);
    };

    /**
     * Memeriksa apakah pengguna memiliki peran tertentu.
     * @param {string} roleName - Nama peran yang akan diperiksa.
     * @returns {boolean} True jika pengguna memiliki peran, false jika tidak.
     */
    app.config.globalProperties.$hasRole = (roleName) => {
      const user = page.props.auth?.user;

      // Jika tidak ada user login, tidak ada peran
      if (!user) {
        return false;
      }
      const userRoles = user.roles || [];
      // Periksa apakah peran yang diminta ada dalam array peran user
      return userRoles.includes(roleName);
    };
  },
};
