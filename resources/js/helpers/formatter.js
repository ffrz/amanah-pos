import dayjs from "dayjs";

export const formatNumber = (value, maxDecimals = 0, locale = "id-ID") => {
  let number = value;

  if (number === null || number === undefined || isNaN(number)) {
    number = 0;
  }

  return new Intl.NumberFormat(locale, {
    minimumFractionDigits: maxDecimals,
    maximumFractionDigits: maxDecimals,
  }).format(number);
};

export function dateTimeFromNow(date) {
  return dayjs(date).fromNow();
}

export function plusMinusSymbol(num) {
  return num > 0 ? "+" : num < 0 ? "-" : "";
}

export function formatNumberWithSymbol(num, maxDecimal = 0) {
  return plusMinusSymbol(num) + formatNumber(Math.abs(num), maxDecimal);
}

export function formatMoneyWithSymbol(num) {
  return plusMinusSymbol(num) + "Rp. " + formatNumber(Math.abs(num));
}

export function formatMoney(num) {
  return "Rp. " + formatNumber(num);
}

export function formatDateTime(
  val,
  fmt = "DD/MM/YYYY HH:mm:ss",
  locale = "id-ID"
) {
  let date;
  if (val instanceof Date) {
    date = val;
  } else if (typeof val === "string") {
    date = new Date(val);
  } else {
    throw new Error("val must be string or Date object");
  }

  return dayjs(date).format(fmt);
}

export function formatDate(val, fmt = "DD/MM/YYYY", locale = "id-ID") {
  return formatDateTime(val, fmt, locale);
}

export function formatTime(val, fmt = "HH:mm:ss", locale = "id-ID") {
  return formatDateTime(val, fmt, locale);
}

export function formatDateTimeForEditing(
  val = new Date(),
  fmt = "YYYY-MM-DD HH:mm:ss"
) {
  return formatDateTime(val, fmt);
}

export function formatDateForEditing(val = new Date(), fmt = "YYYY-MM-DD") {
  return formatDateTime(val, fmt);
}

export function formatTimeForEditing(val = new Date(), fmt = "HH:mm:ss") {
  return formatDateTime(val, fmt);
}

export function formatDateTimeFromNow(val) {
  return dayjs(val).fromNow();
}


/**
 * Membersihkan nomor telepon agar hanya berisi angka dan berawalan 62
 * Contoh: +62 857-7558 -> 628577558
 */
export const cleanPhoneNumber = (phone) => {
  if (!phone) return "";

  let cleaned = phone.toString().replace(/\D/g, "");

  if (cleaned.startsWith("0")) {
    cleaned = "62" + cleaned.substring(1);
  } else if (cleaned.startsWith("8")) {
    cleaned = "62" + cleaned;
  }

  return cleaned;
};

/**
 * Memformat nomor telepon menjadi standar cantik: +62 8xx-xxxx-xxxx
 */
export const formatPhoneNumber = (phone) => {
  const cleaned = cleanPhoneNumber(phone);
  if (!cleaned) return "-";

  // Jika format tidak sesuai standar minimal (misal nomor telp kantor pendek)
  if (cleaned.length < 10) return `+${cleaned}`;

  const country = cleaned.substring(0, 2); // 62
  const prefix = cleaned.substring(2, 5);  // 8xx
  const middle = cleaned.substring(5, 9);  // xxxx
  const end = cleaned.substring(9);       // xxxx...

  let formatted = `+${country} ${prefix}`;
  if (middle) formatted += `-${middle}`;
  if (end) formatted += `-${end}`;

  return formatted;
};
