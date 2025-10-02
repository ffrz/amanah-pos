import { ref } from "vue";

export function useSupplierFilter(suppliersRaw, includeAllOption = false) {
  const baseSuppliers = suppliersRaw.map((item) => {
    return { value: item.id, label: "#" + item.id + " - " + item.name };
  });

  const suppliers = includeAllOption
    ? [
        { value: "all", label: "Semua" },
        { value: "null", label: "Tanpa Supplier" },
        ...baseSuppliers,
      ]
    : baseSuppliers;

  const filteredSuppliers = ref([...suppliers]);

  const filterSupplierFn = (val, update) => {
    const search = val.toLowerCase();
    update(() => {
      filteredSuppliers.value = suppliers.filter((item) =>
        item.label.toLowerCase().includes(search)
      );
    });
  };

  return {
    filteredSuppliers,
    filterSupplierFn,
    suppliers,
  };
}
