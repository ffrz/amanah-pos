import dayjs from "dayjs";

export function getCurrentYear() {
  return new Date().getFullYear();
}

export function getCurrentMonth() {
  return new Date().getMonth() + 1;
}

/**
 * Parse string date (MySQL / ISO) ke Dayjs → Date object
 */
export function dateFromString(str, fmt) {
  if (!str) return null;
  const d = fmt ? dayjs(str, fmt) : dayjs(str);
  return d.isValid() ? d.toDate() : null;
}

/**
 * Format Date ke string sesuai format
 */
export function dateToString(date, fmt = "DD/MM/YYYY") {
  if (!date) return "";
  return dayjs(date).format(fmt);
}

/**
 * Parse datetime string ke Date
 */
export function dateTimeFromString(str, fmt) {
  return dateFromString(str, fmt);
}

/**
 * Format Date ke datetime string
 */
export function dateTimeToString(date, fmt = "DD/MM/YYYY HH:mm:ss") {
  return dateToString(date, fmt);
}
