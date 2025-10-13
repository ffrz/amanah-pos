<script setup>
import { handleFetchItems } from "@/helpers/client-req-handler";
import { getQueryParams } from "@/helpers/utils";
import { router, usePage } from "@inertiajs/vue3";
import { useQuasar } from "quasar";
import { computed, onMounted, reactive, ref } from "vue";
import { formatDateTime, formatNumber } from "@/helpers/formatter";
import LongTextView from "@/components/LongTextView.vue";

const page = usePage();
const rows = ref([]);
const loading = ref(true);
const filter = reactive({
  customer_id: page.props.data.id,
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
  { name: "id", label: "Trx", field: "id", align: "left" },
  {
    name: "grand_total",
    label: "Total",
    field: "grand_total",
    align: "right",
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
    url: route("admin.purchase-order.data", {
      filter: {
        status: ["closed", "canceled"],
        supplier_id: page.props.data.id,
      },
    }),
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
            route('admin.purchase-order.detail', {
              id: props.row.id,
            })
          )
        "
      >
        <q-td key="id" :props="props">
          <div class="text-bold">
            <q-icon class="inline-icon" name="tag" />
            {{ props.row.formatted_id }}
          </div>
          <div>
            <q-icon class="inline-icon" name="calendar_clock" />
            {{ formatDateTime(props.row.datetime) }}
          </div>
          <template v-if="$q.screen.lt.md">
            <div class="text-bold">
              <q-icon name="money" class="inline-icon" />
              Rp. {{ formatNumber(props.row.grand_total) }}
            </div>
            <div v-if="props.row.notes">
              <LongTextView
                :text="props.row.notes"
                class="text-grey-8"
                icon="notes"
              />
            </div>
          </template>
          <div>
            <q-badge :color="props.row.status == 'closed' ? 'green' : 'red'">
              {{ $CONSTANTS.SALES_ORDER_STATUSES[props.row.status] }}
            </q-badge>
            <q-badge
              :color="
                props.row.payment_status == 'fully_paid' ? 'green' : 'red'
              "
              class="q-ml-xs"
            >
              {{
                $CONSTANTS.SALES_ORDER_PAYMENT_STATUSES[
                  props.row.payment_status
                ]
              }}
            </q-badge>
          </div>
        </q-td>
        <q-td key="grand_total" :props="props">
          <div>
            {{ formatNumber(props.row.grand_total) }}
          </div>
        </q-td>
        <q-td key="notes" :props="props" class="wrap-column">
          <LongTextView :text="props.row.notes" />
        </q-td>
      </q-tr>
    </template>
  </q-table>
</template>
