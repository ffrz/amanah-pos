<script setup>
import { formatNumber } from "@/helpers/formatter";
import useTableHeight from "@/composables/useTableHeight";
import LongTextView from "@/components/LongTextView.vue";

const tableHeight = useTableHeight(null, 390);
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
    required: true,
    label: "Item",
    align: "left",
    field: "name",
    sortable: false,
  },
  {
    name: "subtotal_price",
    label: "Sub Total (Rp.)",
    align: "right",
    sortable: false,
  },
  {
    name: "action",
    label: "",
    align: "right",
    sortable: false,
  },
];

defineEmits(["update-quantity", "remove-item", "edit-item"]);
</script>
<template>
  <q-table
    dense
    :rows="items"
    :columns="columns"
    row-key="id"
    flat
    square
    bordered
    class="full-height-table"
    :rows-per-page-options="[0]"
    hide-pagination
    :no-data-label="'Belum ada item'"
    virtual-scroll
    :virtual-scroll-item-size="48"
    :virtual-scroll-sticky-size-start="48"
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
          <div class="text-weight-medium">
            {{ props.row.product_name }}
          </div>
          <div class="text-caption text-grey-6">
            {{ props.row.product_barcode }}
          </div>
          <LongTextView
            v-if="props.row.notes"
            :text="props.row.notes"
            icon="notes"
            class="text-grey-6"
          />
        </q-td>

        <q-td key="subtotal_price" :props="props" class="text-right">
          <div class="column items-end">
            <div>{{ formatNumber(props.row.subtotal_price) }}</div>
            <div class="text-caption text-grey-6 text-italic">
              {{ formatNumber(props.row.quantity) }} x Rp.
              {{ formatNumber(props.row.price) }}
            </div>
          </div>
        </q-td>

        <q-td key="action" :props="props" class="text-right" style="width: 5%">
          <q-btn
            icon="edit"
            color="grey"
            flat
            round
            size="xs"
            @click="$emit('edit-item', props.row)"
            :disable="isProcessing"
          />
          <q-btn
            icon="delete"
            color="negative"
            flat
            round
            size="xs"
            @click="$emit('remove-item', props.row)"
            :disable="isProcessing"
          />
        </q-td>
      </q-tr>
    </template>
  </q-table>
</template>
