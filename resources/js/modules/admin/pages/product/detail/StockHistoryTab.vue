<script setup>
import { handleFetchItems } from "@/helpers/client-req-handler";
import { getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { computed, onMounted, reactive, ref } from "vue";
import {
  formatDateTime,
  formatNumber,
  formatNumberWithSymbol,
} from "@/helpers/formatter";
import LongTextView from "@/components/LongTextView.vue";
import useOpenStockMovementSource from "@/composables/useOpenStockMovementSource";

const props = defineProps({
  productId: {
    type: [String, Number],
    required: true,
  },
});

const $q = useQuasar();
const rows = ref([]);
const loading = ref(true);
const filter = reactive({
  ...getQueryParams(),
});
const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "created_at",
  descending: true,
});

let columns = [
  {
    name: "id",
    label: "Item",
    field: "id",
    align: "left",
    sortable: true,
  },
  {
    name: "quantity_before",
    label: "Stok Awal",
    field: "quantity_before",
    align: "right",
  },
  {
    name: "quantity",
    label: "+/-",
    field: "quantity",
    align: "right",
  },
  {
    name: "quantity_after",
    label: "Stok Akhir",
    field: "quantity_after",
    align: "right",
  },
  {
    name: "uom",
    label: "Satuan",
    field: "uom",
    align: "right",
  },
  {
    name: "party",
    label: "Pihak",
    field: "party",
    align: "left",
    sortable: true,
  },
  {
    name: "notes",
    label: "Catatan",
    field: "notes",
    align: "left",
  },
];

onMounted(() => {
  fetchItems();
});

const fetchItems = (tableProps = null) =>
  handleFetchItems({
    pagination,
    filter,
    props: tableProps,
    rows,
    url: route("admin.stock-movement.data", {
      filter: { product_id: props.productId },
    }),
    loading,
  });

const computedColumns = computed(() => {
  if (!$q.screen.lt.sm) return columns;
  return columns.filter((col) => col.name === "id" || col.name === "quantity");
});

const openDetail = useOpenStockMovementSource;
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
          {{ filter ? " with term " + filter : "" }}</span
        >
      </div>
    </template>

    <template v-slot:body="props">
      <q-tr
        :props="props"
        class="cursor-pointer"
        @click="openDetail(props.row)"
      >
        <q-td key="id" :props="props">
          <div>
            <q-icon class="inline-icon" name="tag" />
            {{ props.row.code }}
            -
            {{ formatDateTime(props.row.created_at) }}
          </div>
          <div>
            <q-icon class="inline-icon" name="person" />
            {{ props.row.creator.username }} |
            {{ props.row.creator.name }}
          </div>
          <div>
            <template v-if="props.row.parent_id">
              <q-icon class="inline-icon" name="tag" />
              {{ props.row.document_code }}
              -
            </template>
            <q-badge>
              {{ $CONSTANTS.STOCK_MOVEMENT_REF_TYPES[props.row.ref_type] }}
            </q-badge>
          </div>
          <template v-if="$q.screen.lt.sm">
            <div
              :class="
                props.row.quantity > 0 ? 'text-positive' : 'text-negative'
              "
            >
              <q-icon class="inline-icon" name="compare_arrows" />
              {{ formatNumber(props.row.quantity_before) }}
              {{ props.row.uom }}
              &rarr;
              {{ formatNumber(props.row.quantity_after) }}
              {{ props.row.uom }}
            </div>
            <LongTextView
              :text="props.row.notes"
              icon="notes"
              class="text-grey-8"
            />
          </template>
        </q-td>
        <q-td key="created_at" :props="props">
          {{ formatDateTime(props.row.created_at) }}
        </q-td>
        <q-td key="type" :props="props">
          {{ $CONSTANTS.STOCK_MOVEMENT_REF_TYPES[props.row.ref_type] }}
          <span v-if="props.row.ref">
            {{ props.row.ref }}
          </span>
        </q-td>
        <q-td key="quantity_before" :props="props">
          {{ formatNumber(props.row.quantity_before) }}
        </q-td>
        <q-td
          key="quantity"
          :props="props"
          :class="props.row.quantity > 0 ? 'text-positive' : 'text-negative'"
        >
          {{ formatNumberWithSymbol(props.row.quantity) }}
          <template v-if="$q.screen.lt.sm">{{ props.row.uom }}</template>
        </q-td>
        <q-td key="quantity_after" :props="props">
          {{ formatNumber(props.row.quantity_after) }}
        </q-td>
        <q-td key="uom" :props="props">
          {{ props.row.uom }}
        </q-td>
        <q-td key="party" :props="props">
          {{
            props.row.party_id
              ? props.row.party_code + " - " + props.row.party_name
              : ""
          }}
        </q-td>
        <q-td key="notes" :props="props">
          <LongTextView :text="props.row.notes" />
        </q-td>
      </q-tr>
    </template>
  </q-table>
</template>
