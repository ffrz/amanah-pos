export function createYearOptions(
  startYear,
  endYear,
  includeAllOption = false,
  reverse = false
) {
  let years = [];

  for (let i = startYear; i <= endYear; i++) {
    years.push({ value: i, label: i.toString() });
  }

  if (reverse) {
    years = years.reverse();
  }

  if (includeAllOption) {
    years.unshift({ value: "all", label: "Semua Tahun" });
  }

  return years;
}


export function createMonthOptions(includeAllOption = false) {
  const months = [
    { value: 1, label: "Januari" },
    { value: 2, label: "Februari" },
    { value: 3, label: "Maret" },
    { value: 4, label: "April" },
    { value: 5, label: "Mei" },
    { value: 6, label: "Juni" },
    { value: 7, label: "Juli" },
    { value: 8, label: "Agustus" },
    { value: 9, label: "September" },
    { value: 10, label: "Oktober" },
    { value: 11, label: "November" },
    { value: 12, label: "Desember" },
  ];

  if (includeAllOption) {
    return [{ value: "all", label: "Semua Bulan" }, ...months];
  }

  return months;
}


export function createOptions(data) {
  return Object.entries(data)
    .map(([key, value]) => ({ 'value': key, 'label': value }));
}

