<script setup>
defineProps({
  user: {
    type: Object,
    required: true,
  },
  company: {
    type: Object,
    required: true,
  },
  currentDate: {
    type: String,
    required: true,
  },
  currentTime: {
    type: String,
    required: true,
  },
});

defineEmits(["edit-supplier"]);
</script>

<template>
  <div class="row items-center q-px-md q-py-xs">
    <div class="col-4">
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

    <div class="col-4">
      <div class="text-h6 text-weight-bold text-grey-8 text-center">
        {{ company.name }}
      </div>
      <div
        v-if="company.address"
        class="text-subtitle2 text-weight-bold text-grey-8 text-center"
      >
        {{ company.address }}
      </div>
      <div
        v-if="company.phone"
        class="text-subtitle2 text-weight-bold text-grey-8 text-center"
      >
        {{ company.phone }}
      </div>
    </div>

    <div class="col-4">
      <div class="text-right">
        <div class="text-weight-bold">
          {{ user.username }} - {{ user.name }}
        </div>
        <div class="text-grey-6">{{ currentDate }} - {{ currentTime }}</div>
      </div>
    </div>
  </div>
</template>
