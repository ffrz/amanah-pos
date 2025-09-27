import { createI18n } from "vue-i18n";

// Define your translations
const messages = {
  en: {
    // menu
    dashboard: "Dashboard",

    welcome: "Welcome to our application!",
    greeting: "Hello, {name}!",
  },
  id: {
    // menu
    add: "Tambah",
    filter: "Penyaringan",
    search: "Cari",

    dashboard: "Dasbor",
    stock_adjustment: "Penyesuaian Stok",
    customers: "Pelanggan",
    suppliers: "Pemasok",
    sales_orders: "Penjualan",
    purchase_orders: "Pembelian",
    products: "Produk",
    product_categories: "Kategori Produk",
    users: "Pengguna",
    settings: "Pengaturan",
    my_profile: "Profil Saya",
    company_profile: "Profil Perusahaan",
    logout: "Keluar",

    add_user: "Tambah Pengguna",
    edit_user: "Edit Pengguna",
    delete_user: "Hapus Pengguna",

    welcome: "Selamat datang di aplikasi kami!",
    greeting: "Selamat datang, {name}!",
  },
};

const i18n = createI18n({
  locale: "id", // Default locale
  messages, // Translations for each locale
});

export default i18n;
