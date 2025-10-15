<script setup>
import { handleFetchItems } from "@/helpers/client-req-handler";
import { getQueryParams } from "@/helpers/utils";
import { ref, reactive, onMounted, computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useQuasar } from "quasar";
import {
  formatDateTime,
  formatNumber,
  plusMinusSymbol,
} from "@/helpers/formatter";
import LongTextView from "@/components/LongTextView.vue";
import useTableHeight from "@/composables/useTableHeight";

const page = usePage();
const $q = useQuasar();
const tableRef = ref(null);
const rows = ref([]);
const loading = ref(true);
const filter = reactive({
  ...getQueryParams(),
});

const tableHeight = useTableHeight(null, 67 + 50);

const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "id",
  descending: true,
});

const columns = [
  {
    name: "code",
    label: "Kode",
    field: "code",
    align: "left",
    sortable: true,
  },
  {
    name: "datetime",
    label: "Waktu",
    field: "datetime",
    align: "left",
    sortable: true,
  },
  {
    name: "type",
    label: "Jenis Trx",
    field: "type",
    align: "center",
  },
  {
    name: "amount",
    label: "Jumlah",
    field: "amount",
    align: "right",
  },
  {
    name: "notes",
    label: "Catatan",
    field: "notes",
    align: "left",
  },
];

const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter((col) => col.name === "id");
});

onMounted(() => {
  fetchItems();
});

const fetchItems = (props = null) =>
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.finance-transaction.data", {
      filter: { account_id: page.props.data.id },
    }),
    loading,
    tableRef,
  });
</script>

<template>
  <q-table
    ref="tableRef"
    flat
    bordered
    square
    :style="{ height: tableHeight }"
    color="primary"
    class="full-height-table"
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

    <template v-slot:no-data="{ message, filter: filterTerm }">
      <div class="full-width row flex-center text-grey-8 q-gutter-sm">
        <span>
          {{ message }}
          {{ filterTerm ? " with term " + filterTerm : "" }}</span
        >
      </div>
    </template>

    <template v-slot:body="props">
      <q-tr :props="props">
        <q-td key="code" :props="props">
          <div class="flex q-gutter-sm">
            <div>
              <template v-if="$q.screen.lt.md">
                <q-icon name="tag" class="inline-icon" />
              </template>
              {{ props.row.code }}
            </div>
          </div>
          <template v-if="$q.screen.lt.md">
            <div>
              <q-icon name="calendar_clock" class="inline-icon" />
              {{ formatDateTime(props.row.datetime) }}
            </div>
            <div v-if="props.row.creator">
              <q-icon name="person" class="inline-icon" />
              {{ props.row.creator.username }}
            </div>
            <div
              :class="props.row.amount > 0 ? 'text-green' : 'text-red'"
              class="text-bold"
            >
              <q-icon class="inline-icon" name="money" />
              {{ plusMinusSymbol(props.row.amount) }}Rp.
              {{ formatNumber(Math.abs(props.row.amount)) }}
            </div>
            <div>
              <q-badge :color="props.row.amount > 0 ? 'green' : 'red'">
                {{ $CONSTANTS.FINANCE_TRANSACTION_TYPES[props.row.type] }}
              </q-badge>
            </div>
            <LongTextView
              :text="props.row.notes"
              icon="notes"
              class="text-grey-8"
            />
          </template>
        </q-td>
        <q-td key="datetime" :props="props">
          {{ formatDateTime(props.row.datetime) }}
        </q-td>
        <q-td key="type" :props="props">
          <q-badge :color="props.row.amount > 0 ? 'green' : 'red'">
            {{ $CONSTANTS.FINANCE_TRANSACTION_TYPES[props.row.type] }}
          </q-badge>
        </q-td>
        <q-td
          key="amount"
          :props="props"
          :class="props.row.amount > 0 ? 'text-green' : 'text-red'"
        >
          {{
            plusMinusSymbol(props.row.amount) +
            formatNumber(Math.abs(props.row.amount))
          }}
        </q-td>
        <q-td key="notes" :props="props">
          <LongTextView :text="props.row.notes" :icon="notes" />
        </q-td>
      </q-tr>
    </template>
  </q-table>
</template>
