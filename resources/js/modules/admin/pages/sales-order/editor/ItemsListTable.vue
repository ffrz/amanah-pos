<script setup>
import { formatNumber } from "@/helpers/formatter";

defineProps({
  items: {
    type: Array,
    required: true,
  },
  columns: {
    type: Array,
    required: true,
  },
});

defineEmits(["update-quantity", "remove-item"]);
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
    class="bg-grey-1 pos-table q-pa-none col full-height-table"
    :rows-per-page-options="[0]"
    hide-pagination
    :no-data-label="'Belum ada item'"
    virtual-scroll
    :virtual-scroll-item-size="48"
    :virtual-scroll-sticky-size-start="48"
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
          <div class="text-weight-medium">{{ props.row.name }}</div>
          <div class="text-caption text-grey-6">
            Barcode: {{ props.row.barcode }}
          </div>
        </q-td>

        <q-td key="subtotal" :props="props" class="text-right">
          <div class="column items-end">
            <div class="text-weight-bold text-primary">
              Rp. {{ formatNumber(props.row.price * props.row.quantity) }}
            </div>
            <div class="text-caption text-grey-6 text-italic">
              {{ props.row.quantity }} x Rp. {{ formatNumber(props.row.price) }}
            </div>
          </div>
        </q-td>

        <q-td key="action" :props="props" class="text-right">
          <q-btn
            icon="delete"
            color="negative"
            flat
            round
            size="sm"
            @click="$emit('remove-item', props.row)"
          />
        </q-td>
      </q-tr>
    </template>
  </q-table>
</template>
