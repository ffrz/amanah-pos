import { usePage } from "@inertiajs/vue3";

export const getQueryParams = (...args) => {
  const page = usePage();
  let queryString = page.url;
  if (queryString.indexOf("?") === -1) {
    return {};
  }
  queryString = queryString.substring(queryString.indexOf("?") + 1);
  return Object.assign(
    Object.fromEntries(new URLSearchParams(queryString)),
    ...args
  );
};

export async function scrollToFirstErrorField(ref) {
  const element = ref.getNativeElement();
  if (element) {
    element.scrollIntoView({ behavior: "smooth", block: "center" });
    element.focus();
  }
}

/**
 * Menghasilkan URL wa.me non-reaktif.
 * Cocok untuk dipanggil sekali saat event klik atau data statis.
 * * @param {string} phone Nomor telepon (misal: '0812 3456 7890')
 * @param {string} message Pesan teks (non-encoded)
 * @returns {string} URL wa.me yang diformat atau '#' jika tidak valid
 */
export function waMeUrl(phone, message = "") {
  // 1. Validasi
  if (!phone || typeof phone !== "string" || phone.length > 15) {
    return "#";
  }

  let phoneNumber = phone;

  // 2. Pembersihan
  // Hapus spasi, strip, dan karakter non-digit lainnya (kecuali mungkin '+' yang akan kita tangani)
  phoneNumber = phoneNumber.replace(/[^0-9+]/g, "");

  // 3. Format Awalan
  if (phoneNumber.startsWith("0")) {
    // Ganti '0' pertama dengan '62'
    phoneNumber = "62" + phoneNumber.substring(1);
  }
  // Jika sudah diawali '+62', biarkan saja, wa.me biasanya bisa menanganinya

  // 4. URL Encoding Pesan
  const encodedMessage = encodeURIComponent(message);

  // 5. Generate URL
  return `https://wa.me/${phoneNumber}?text=${encodedMessage}`;
}
