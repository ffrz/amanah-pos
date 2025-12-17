<script setup>
import { formatNumber } from "@/helpers/formatter";
import useTableHeight from "@/composables/useTableHeight";
import LongTextView from "@/components/LongTextView.vue";
import { useQuasar } from "quasar";
import { computed } from "vue";

const $q = useQuasar();
const tableHeight = useTableHeight(null, $q.screen.lt.sm ? 395 : 310);
const porps = defineProps({
  items: {
    type: Array,
    required: true,
  },
  isProcessing: {
    type: Boolean,
    default: false,
  },
});

const columns = [
  {
    name: "name",
    label: "Produk / Item",
    align: "left",
    field: "product_name",
  },
  {
    name: "quantity",
    label: "Qty",
    align: "center",
    field: "quantity",
    style: "width: 80px",
  },
  {
    name: "price",
    label: "Harga (@)",
    align: "right",
    field: "price",
    style: "width: 150px",
  },
  {
    name: "subtotal",
    label: "Subtotal (Rp)",
    align: "right",
    field: "subtotal_price",
    style: "width: 180px",
  },
  {
    name: "action",
    label: "",
    align: "center",
    sortable: false,
    style: "width: 100px",
  },
];

defineEmits(["update-quantity", "remove-item", "edit-item"]);

const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter(
    (col) =>
      col.name === "name" || col.name === "subtotal" || col.name === "action"
  );
});
</script>
<template>
  <q-table
    dense
    :rows="items"
    :columns="computedColumns"
    row-key="id"
    flat
    square
    bordered
    class="full-height-table"
    :rows-per-page-options="[0]"
    hide-pagination
    :no-data-label="'Belum ada item'"
    :style="{ height: tableHeight }"
  >
    <template v-slot:header="props">
      <q-tr :props="props" class="bg-grey-4">
        <q-th
          v-for="col in props.cols"
          :key="col.name"
          :props="props"
          class="text-weight-bold text-grey-8"
        >
          {{ col.label }}
        </q-th>
      </q-tr>
    </template>

    <template v-slot:body="props">
      <q-tr :props="props" class="hover-highlight">
        <q-td key="name" :props="props" class="text-left">
          <div class="text-weight-medium wrap-column">
            {{ props.row.product_name }}
          </div>
          <div class="text-caption text-grey-6 elipsis">
            {{ props.row.product_barcode }}
          </div>
          <LongTextView
            v-if="props.row.notes"
            :text="props.row.notes"
            icon="notes"
            class="text-grey-6"
          />
        </q-td>

        <q-td key="quantity" :props="props">
          <div class="text-weight-medium">
            {{ formatNumber(props.row.quantity) }} {{ props.row.product_uom }}
          </div>
        </q-td>

        <q-td key="price" :props="props">
          {{ formatNumber(props.row.price) }}
        </q-td>

        <q-td key="subtotal" :props="props" class="text-right">
          <div v-if="!$q.screen.gt.sm" class="column items-end">
            <div>{{ formatNumber(props.row.subtotal_price) }}</div>
            <div class="text-caption text-grey-6 text-italic">
              {{ formatNumber(props.row.quantity) }} x Rp.
              {{ formatNumber(props.row.price) }}
            </div>
          </div>
          <div v-else class="text-weight-bold">
            {{ formatNumber(props.row.subtotal_price) }}
          </div>
        </q-td>

        <q-td key="action" :props="props" class="text-right" style="width: 5%">
          <q-btn
            icon="edit"
            color="grey"
            flat
            round
            size="sm"
            @click="$emit('edit-item', props.row)"
            :disable="isProcessing"
          />
          <q-btn
            icon="delete"
            color="negative"
            flat
            round
            size="sm"
            @click="$emit('remove-item', props.row)"
            :disable="isProcessing"
          />
        </q-td>
      </q-tr>
    </template>
  </q-table>
</template>
