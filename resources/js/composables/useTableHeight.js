import { computed, ref, watch, nextTick } from "vue";

/**
 * Composables to dynamically calculate table height based on a filter toolbar.
 * @param {import('vue').Ref<HTMLElement | null>} filterRef The ref of the filter toolbar element.
 */
export default function useTableHeight(filterRef, headerHeight = 59) {
  const filterHeight = ref(0);

  if (filterRef) {
    watch(
      () => filterRef.value,
      (newValue) => {
        if (newValue) {
          nextTick(() => {
            filterHeight.value = newValue.$el
              ? newValue.$el.offsetHeight
              : newValue.offsetHeight;
          });
        } else {
          filterHeight.value = 0;
        }
      }
    );
  }

  const tableHeight = computed(() => {
    return `calc(100vh - ${headerHeight}px - ${filterHeight.value}px)`;
  });

  return tableHeight;
}
