<script setup>
import { computed } from "vue";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import PercentInput from "@/components/PercentInput.vue";

const props = defineProps({
  label: { type: String, required: true },
  cost: { type: Number, default: 0 }, // Modal dasar untuk perhitungan
  price: { type: Number, default: 0 }, // v-model:price
  markup: { type: Number, default: 0 }, // v-model:markup
  tiers: { type: Array, default: () => [] }, // Array Tier
});

const emit = defineEmits(["update:price", "update:markup", "update:tiers"]);

// --- LOGIKA SINKRONISASI OTOMATIS (Bi-Directional) ---

// 1. Jika User Input HARGA (Rupiah) -> Hitung Markup
const onUpdatePrice = (newPrice) => {
  const val = parseFloat(newPrice) || 0;
  emit("update:price", val);

  // Jangan hitung jika modal 0 untuk menghindari Infinity
  if (props.cost > 0) {
    const newMarkup = ((val - props.cost) / props.cost) * 100;
    emit("update:markup", parseFloat(newMarkup.toFixed(2)));
  }
};

// 2. Jika User Input MARKUP (%) -> Hitung Harga
const onUpdateMarkup = (newMarkup) => {
  const val = parseFloat(newMarkup) || 0;
  emit("update:markup", val);

  if (props.cost > 0) {
    const newPrice = props.cost * (1 + val / 100);
    // Pembulatan harga (Opsional: bisa diatur pembulatan ke ratusan terdekat)
    emit("update:price", Math.round(newPrice));
  }
};

// --- LOGIKA TIER / HARGA BERJENJANG ---

const addTier = () => {
  const newTiers = [...props.tiers];
  // Default tier: Min Qty 10, Harga = Harga saat ini
  newTiers.push({ min_qty: 10, price: props.price });
  emit("update:tiers", newTiers);
};

const removeTier = (index) => {
  const newTiers = [...props.tiers];
  newTiers.splice(index, 1);
  emit("update:tiers", newTiers);
};
</script>

<template>
  <div class="q-pa-sm bg-white rounded-borders">
    <div class="row items-center q-col-gutter-sm">
      <div class="col-12 col-md-3 text-weight-medium">
        {{ label }}
      </div>

      <div class="col-6 col-md-3">
        <PercentInput
          dense
          outlined
          :model-value="markup"
          @update:model-value="onUpdateMarkup"
          label="Markup %"
          :max-decimals="2"
          hide-bottom-space
        />
      </div>

      <div class="col-6 col-md-4">
        <LocaleNumberInput
          dense
          outlined
          :model-value="price"
          @update:model-value="onUpdatePrice"
          label="Harga Jual"
          hide-bottom-space
        />
      </div>

      <div class="col-12 col-md-2 text-right">
        <q-btn
          flat
          dense
          size="sm"
          color="primary"
          icon="add_circle"
          label="Harga Qty"
          @click="addTier"
        >
          <q-tooltip>Tambah harga khusus pembelian banyak</q-tooltip>
        </q-btn>
      </div>
    </div>

    <div
      v-if="tiers && tiers.length > 0"
      class="q-mt-sm q-ml-md q-pl-md border-left-indicator"
    >
      <div class="text-caption text-primary q-mb-xs">
        Harga Berjenjang (Quantity Break):
      </div>
      <div
        v-for="(tier, index) in tiers"
        :key="index"
        class="row q-col-gutter-sm q-mb-xs items-center"
      >
        <div class="col-3">
          <LocaleNumberInput
            dense
            outlined
            v-model="tier.min_qty"
            label="Qty >="
            bg-color="white"
          />
        </div>
        <div class="col-4">
          <LocaleNumberInput
            dense
            outlined
            label="Harga"
            v-model="tier.price"
            bg-color="white"
          />
        </div>
        <div class="col-auto">
          <q-btn
            flat
            round
            dense
            color="red"
            icon="close"
            size="sm"
            @click="removeTier(index)"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.border-left-indicator {
  border-left: 3px solid #e0e0e0;
}
/* Hover effect biar UX lebih enak */
.border-left-indicator:hover {
  border-left: 3px solid #1976d2;
}
</style>
