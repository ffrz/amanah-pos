export function getCurrentMonth() {
  return new Date().getFullYear();
}

export function getCurrentYear() {
  return new Date().getMonth() + 1;
}
