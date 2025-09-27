import { ref } from "vue";

export function useCustomerFilter(rawItems, includeAllOption = false) {
  const basecustomers = rawItems.map((item) => {
    return { value: item.id, label: item.code + " - " + item.name };
  });

  const customers = includeAllOption
    ? [{ value: "all", label: "Semua" }, ...basecustomers]
    : basecustomers;

  const filteredCustomers = ref([...customers]);

  const filterCustomersFn = (val, update) => {
    update(() => {
      filteredCustomers.value = customers.filter((item) =>
        item.label.toLowerCase().includes(val.toLowerCase())
      );
    });
  };

  return {
    filteredCustomers,
    filterCustomersFn,
    customers,
  };
}
