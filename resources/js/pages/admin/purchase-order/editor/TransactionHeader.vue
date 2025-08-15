<script setup>
import DigitalClock from "@/components/DigitalClock.vue";

defineProps({
  user: {
    type: Object,
    required: true,
  },
  company: {
    type: Object,
    required: true,
  },
});

defineEmits(["edit-supplier"]);
</script>
<template>
  <div class="row q-col-gutter-md items-center q-px-md q-py-sm">
    <div class="col-12 col-sm-6 col-md-4">
      <div class="text-bold">
        Info Supplier
        <q-btn
          icon="edit"
          flat
          rounded
          dense
          color="grey"
          size="sm"
          @click="$emit('edit-supplier')"
        />
      </div>
      <div class="text-grey-8 text-italic">Supplier belum dipilih.</div>
    </div>

    <div v-if="$q.screen.gt.sm" class="col-12 col-md-4 text-center">
      <div class="text-h6 text-weight-bold text-grey-8">
        {{ company.name }}
      </div>
      <div
        v-if="company.address"
        class="text-subtitle2 text-weight-bold text-grey-8"
      >
        {{ company.address }}
      </div>
      <div
        v-if="company.phone"
        class="text-subtitle2 text-weight-bold text-grey-8"
      >
        {{ company.phone }}
      </div>
    </div>

    <div
      class="col-12 col-sm-6 col-md-4"
      :class="$q.screen.gt.sm ? 'text-right' : ''"
    >
      <div class="text-weight-bold">{{ user.username }} - {{ user.name }}</div>
      <DigitalClock />
    </div>
  </div>
</template>
