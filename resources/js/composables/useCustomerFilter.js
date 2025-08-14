import { ref } from 'vue';

export function useCustomerFilter(rawItems, includeAllOption = false) {
  const baseSuppliers = rawItems.map((item) => {
    return { value: item.id, label: item.nis + ' - ' + item.name };
  });

  const suppliers = includeAllOption
    ? [{ value: 'all', label: 'Semua' }, ...baseSuppliers]
    : baseSuppliers;

  const filteredCustomers = ref([...suppliers]);

  const filterCustomersFn = (val, update) => {
    update(() => {
      filteredCustomers.value = suppliers.filter(item =>
        item.label.toLowerCase().includes(val.toLowerCase())
      );
    });
  };

  return {
    filteredCustomers,
    filterCustomersFn,
    suppliers,
  };
}
