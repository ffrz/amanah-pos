import { ref } from 'vue';

export function useCostCategoryFilter(rawCategories, includeAllOption = false) {
  const baseCategories = rawCategories.map(item => ({
    value: item.id,
    label: item.name
  }));

  const costCategoryOptions = includeAllOption
    ? [{ value: 'all', label: 'Semua' }, ...baseCategories]
    : baseCategories;

  const filteredCostCategories = ref([...costCategoryOptions]);

  const filterCostCategory = (val, update) => {
    const search = val.toLowerCase();
    update(() => {
      filteredCostCategories.value = costCategoryOptions.filter(item =>
        item.label.toLowerCase().includes(search)
      );
    });
  };

  return {
    filteredCostCategories,
    filterCostCategory,
    costCategoryOptions // jika butuh juga yang belum difilter
  };
}
