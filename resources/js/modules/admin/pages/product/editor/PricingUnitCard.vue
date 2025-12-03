<script setup>
defineProps({
  icon: { type: String, default: "inventory_2" },
  title: { type: String, required: true },
  label: { type: String, default: "" }, // Nama satuannya
  badgeLabel: { type: String, default: "" }, // Isi badge (misal: "1 Unit" atau "Isi 12")
  badgeColor: { type: String, default: "blue" },
  cost: { type: Number, default: 0 },
});
</script>

<template>
  <div class="q-px-md">
    <q-card flat bordered class="bg-grey-1 full-width">
      <q-card-section :class="badgeColor === 'blue' ? '' : 'bg-grey-2'">
        <div class="text-subtitle1 text-weight-bold row items-center">
          <q-icon :name="icon" class="q-mr-sm" />
          {{ title }}: {{ label }}
          <q-badge :color="badgeColor" class="q-ml-md">
            {{ badgeLabel }}
          </q-badge>
        </div>
        <div class="text-caption text-grey-8">
          Modal:
          <b>Rp {{ Number(cost).toLocaleString("id-ID") }}</b>
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section class="q-pa-none">
        <slot />
      </q-card-section>
    </q-card>
  </div>
</template>
