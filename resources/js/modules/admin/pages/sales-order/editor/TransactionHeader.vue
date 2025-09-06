<template>
  <div class="row q-col-gutter-md items-center q-px-md q-py-sm">
    <div class="col-12 col-sm-6 col-md-4">
      <div>
        <CustomerAutocomplete
          class="custom-select"
          v-model="customer.id"
          label="Pelanggan"
          :disable="formProcessing"
          @customer-selected="updateCustomerData"
        />
        <div v-if="customer.data" class="text-grey q-mt-xs">
          Saldo: Rp. {{ formatNumber(customer.data.balance) }}
        </div>
      </div>
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
      v-if="$q.screen.gt.sm"
      class="col-12 col-sm-6 col-md-4"
      :class="$q.screen.gt.sm ? 'text-right' : 'text-center'"
    >
      <div class="text-weight-bold">{{ user.username }} - {{ user.name }}</div>
      <DigitalClock />
    </div>
  </div>
</template>

<script setup>
import CustomerAutocomplete from "@/components/CustomerAutocomplete.vue";
import DigitalClock from "@/components/DigitalClock.vue";
import { formatNumber } from "@/helpers/formatter";
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
  company: {
    type: Object,
    required: true,
  },
  formProcessing: {
    type: Boolean,
    default: false,
  },
});

const customer = ref({
  id: null,
  data: null,
});

function updateCustomerData(data) {
  customer.value.data = data;
}
</script>
