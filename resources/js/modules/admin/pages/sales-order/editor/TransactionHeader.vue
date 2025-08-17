<script setup>
import DigitalClock from "@/components/DigitalClock.vue";
import { useCustomerFilter } from "@/composables/useCustomerFilter";
import { usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const page = usePage();
const { filteredCustomers, filterCustomersFn, customers } = useCustomerFilter(
  page.props.customers,
  false
);

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
  company: {
    type: Object,
    required: true,
  },
  modelValue: {
    type: [Object, null],
    default: null,
  },
  formProcessing: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:modelValue"]);
const customer = ref(null);
</script>

<template>
  <div class="row q-col-gutter-md items-center q-px-md q-py-sm">
    <div class="col-12 col-sm-6 col-md-4">
      <q-select
        class="custom-select"
        v-model="customer"
        label="Santri"
        use-input
        input-debounce="300"
        clearable
        map-options
        emit-value
        :options="customers"
        @filter="filterCustomersFn"
        :disable="formProcessing"
      >
        <template v-slot:no-option>
          <q-item>
            <q-item-section>Santri tidak ditemukan</q-item-section>
          </q-item>
        </template>
      </q-select>
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
      :class="$q.screen.gt.sm ? 'text-right' : 'text-center'"
    >
      <div class="text-weight-bold">{{ user.username }} - {{ user.name }}</div>
      <DigitalClock />
    </div>
  </div>
</template>
