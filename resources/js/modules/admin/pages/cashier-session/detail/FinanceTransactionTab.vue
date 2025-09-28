<script setup>
import { handleFetchItems } from "@/helpers/client-req-handler";
import { getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { computed, onMounted, reactive, ref } from "vue";
import {
  formatDateTime,
  formatDateTimeForEditing,
  formatNumber,
} from "@/helpers/formatter";
import LongTextView from "@/components/LongTextView.vue";

const props = defineProps({
  data: Object,
});
console.log(props.data);
const rows = ref([]);
const loading = ref(true);
const filter = reactive({
  ...getQueryParams(),
  user_id: props.data.user_id,
  from_datetime: formatDateTimeForEditing(props.data.opened_at),
  to_datetime: props.data.closed_at
    ? formatDateTimeForEditing(props.data.closed_at)
    : null,
});
const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "id",
  descending: true,
});

const columns = [
  { name: "id", label: "ID", field: "id", align: "left", sortable: true },

  {
    name: "amount",
    label: "Jumlah (Rp)",
    field: "amount",
    align: "right",
    sortable: true,
  },
  { name: "notes", label: "Catatan", field: "notes", align: "left" },
];

onMounted(() => {
  fetchItems();
});

const fetchItems = (props = null) =>
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.finance-transaction.data"),
    loading,
  });

const $q = useQuasar();
const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter((col) => col.name === "id");
});
</script>

<template>
  <q-table
    flat
    bordered
    square
    color="primary"
    class="full-height-table full-height-table2"
    row-key="id"
    v-model:pagination="pagination"
    :filter="filter.search"
    :loading="loading"
    :columns="computedColumns"
    :rows="rows"
    :rows-per-page-options="[10, 25, 50]"
    @request="fetchItems"
    binary-state-sort
  >
    <template v-slot:loading>
      <q-inner-loading showing color="red" />
    </template>

    <template v-slot:no-data="{ icon, message, filter }">
      <div class="full-width row flex-center text-grey-8 q-gutter-sm">
        <span>
          {{ message }}
          {{ filter ? " with term " + filter : "" }}
        </span>
      </div>
    </template>

    <template v-slot:body="props">
      <q-tr :props="props">
        <q-td key="id" :props="props">
          <div class="text-bold">
            <q-icon class="inline-icon" name="tag" />
            {{ props.row.formatted_id }}
          </div>
          <div>
            <q-icon class="inline-icon" name="calendar_today" />
            {{ formatDateTime(props.row.datetime) }}
          </div>
          <template v-if="$q.screen.lt.md">
            <div class="text-bold">
              <q-icon name="money" class="inline-icon" />
              Rp. {{ formatNumber(props.row.amount) }}
            </div>
            <div v-if="props.row.notes">
              <LongTextView
                :text="props.row.notes"
                class="text-grey-8"
                icon="notes"
              />
            </div>
          </template>
          <div class="q-mt-sm">
            <q-badge :color="props.row.amount >= 0 ? 'green' : 'red'">
              {{ $CONSTANTS.FINANCE_TRANSACTION_TYPES[props.row.type] }}
            </q-badge>
          </div>
        </q-td>
        <q-td key="amount" :props="props">
          <span :class="props.row.amount < 0 ? 'text-red' : 'text-green'">
            {{ formatNumber(props.row.amount) }}
          </span>
        </q-td>
        <q-td key="notes" :props="props" class="wrap-column">
          <LongTextView :text="props.row.notes" />
        </q-td>
      </q-tr>
    </template>
  </q-table>
</template>
