<script setup>
import { handleFetchItems } from "@/helpers/client-req-handler";
import { getQueryParams } from "@/helpers/utils";
import { router, usePage } from "@inertiajs/vue3";
import { useQuasar } from "quasar";
import { computed, onMounted, reactive, ref } from "vue";
import { formatDateTime, formatMoney } from "@/helpers/formatter";
import LongTextView from "@/components/LongTextView.vue";

const page = usePage();
const rows = ref([]);
const loading = ref(true);
const filter = reactive({
  cashier_terminal_id: page.props.data.id,
  ...getQueryParams(),
});
const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "id",
  descending: true,
});

const columns = [
  {
    name: "id",
    label: "Sesi",
    field: "id",
    align: "left",
    sortable: true,
  },
  {
    name: "opening_info",
    label: "Buka Sesi",
    field: "opening_info",
    sortable: false,
    align: "left",
  },
  {
    name: "closing_info",
    label: "Tutup Sesi",
    field: "closing_info",
    sortable: false,
    align: "left",
  },
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
    url: route("admin.cashier-session.data"),
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
      <q-tr
        :props="props"
        class="cursor-pointer"
        @click.stop="
          router.get(
            route('admin.customer-wallet-transaction.detail', {
              id: props.row.id,
            })
          )
        "
      >
        <q-td key="id" :props="props" class="wrap-column">
          <div>
            <q-icon class="inline-icon" name="tag" />
            Session ID: {{ props.row.id }}
          </div>
          <div>
            <q-icon class="inline-icon" name="point_of_sale" />
            {{ props.row.cashier_terminal.name }}
          </div>
          <div>
            <q-icon class="inline-icon" name="person" />
            {{ props.row.user.username }} - {{ props.row.user.name }}
          </div>
        </q-td>
        <q-td key="opening_info" :props="props">
          <div>
            <q-icon class="inline-icon" name="calendar_clock" />
            {{
              props.row.opened_at ? formatDateTime(props.row.opened_at) : "-"
            }}
          </div>
          <div>
            <q-icon class="inline-icon" name="money" />
            {{ formatMoney(props.row.opening_balance) }}
          </div>
          <LongTextView
            v-if="props.row.opening_notes"
            :text="props.row.opening_notes"
            icon="notes"
          />
        </q-td>

        <q-td key="closing_info" :props="props">
          <div>
            <q-icon class="inline-icon" name="calendar_clock" />
            {{
              props.row.closed_at ? formatDateTime(props.row.closed_at) : "-"
            }}
          </div>
          <div>
            <q-icon class="inline-icon" name="money" />
            {{ formatMoney(props.row.closing_balance) }}
          </div>
          <LongTextView
            v-if="props.row.closing_notes"
            :text="props.row.closing_notes"
            icon="notes"
          />
        </q-td>
      </q-tr>
    </template>
  </q-table>
</template>
