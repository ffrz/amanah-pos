import { ref } from "vue";

export function useProductBrandFilter(
  rawBrands,
  includeAllOption = false
) {
  const baseBrands = rawBrands.map((item) => ({
    value: item.id,
    label: item.name,
  }));

  const brands = includeAllOption
    ? [
      { value: "all", label: "Semua" },
      { value: "null", label: "Tanpa Merk" },
      ...baseBrands,
    ]
    : baseBrands;

  const filteredBrands = ref([...brands]);

  const filterBrands = (val, update) => {
    const search = val.toLowerCase();
    update(() => {
      filteredBrands.value = brands.filter((item) =>
        item.label.toLowerCase().includes(search)
      );
    });
  };

  return {
    filteredBrands,
    filterBrands,
    brands,
  };
}
