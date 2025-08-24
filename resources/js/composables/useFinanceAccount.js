import { ref } from 'vue';

export function useFinanceAccount(items, includeAllOption = false) {
  const baseAccounts = items.map((item) => {
    let label = '';
    if (item.type == 'bank') {
      label += item.name + ' - ' + item.bank + ' ' + item.number + ' an ' + item.holder;
    }
    return { value: item.id, label: label };
  });

  const accountOptions = includeAllOption
    ? [{ value: 'all', label: 'Semua' }, ...baseAccounts]
    : baseAccounts;

  const filteredAccountOptions = ref([...accountOptions]);

  const filterAccountOptions = (val, update) => {
    update(() => {
      filteredAccounts.value = accountOptions.filter(item =>
        item.label.toLowerCase().includes(val.toLowerCase())
      );
    });
  };

  return {
    filteredAccountOptions,
    filterAccountOptions,
    accountOptions,
  };
}
