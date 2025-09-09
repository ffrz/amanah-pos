<template>
  <q-input
    v-model="displayDate"
    :label="props.label"
    :readonly="props.readonly"
    :disable="props.disable"
    :error="props.error"
    :rules="props.rules"
    :error-message="props.errorMessage"
    mask="##/##/####"
  >
    <template v-slot:append>
      <q-icon name="event" class="cursor-pointer">
        <q-popup-proxy cover transition-show="scale" transition-hide="scale">
          <q-date
            v-model="dbDate"
            :options="dateOptions"
            @update:model-value="updateDate"
          >
            <div class="row items-center justify-end">
              <q-btn v-close-popup label="Close" color="primary" flat />
            </div>
          </q-date>
        </q-popup-proxy>
      </q-icon>
    </template>
  </q-input>
</template>

<script setup>
import { ref, watch, defineEmits, computed } from "vue";
import { date as QuasarDate } from "quasar";

const props = defineProps({
  modelValue: {
    type: [String, null],
    required: false,
    default: null,
  },
  label: String,
  readonly: Boolean,
  disable: Boolean,
  error: Boolean,
  errorMessage: String,
  rules: {
    type: Array,
    default: () => [],
  },
  minDate: {
    type: String,
    default: null,
  },
  maxDate: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(["update:modelValue"]);

const displayDate = ref("");
const dbDate = ref("");

const displayFormat = "DD/MM/YYYY";
const dbFormat = "YYYY-MM-DD";

const dateOptions = (dateStr) => {
  let isAllowed = true;

  if (props.minDate) {
    isAllowed =
      isAllowed &&
      QuasarDate.isBetweenDates(
        dateStr,
        QuasarDate.formatDate(props.minDate, "YYYY/MM/DD"),
        "2999/01/01",
        { inclusiveFrom: true }
      );
  }

  if (props.maxDate) {
    isAllowed =
      isAllowed &&
      QuasarDate.isBetweenDates(
        dateStr,
        "1900/01/01",
        QuasarDate.formatDate(props.maxDate, "YYYY/MM/DD"),
        { inclusiveTo: true }
      );
  }

  return isAllowed;
};

watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue) {
      dbDate.value = newValue;
      displayDate.value = QuasarDate.formatDate(newValue, displayFormat);
    } else {
      dbDate.value = null;
      displayDate.value = null;
    }
  },
  { immediate: true }
);

const updateDate = (newDate) => {
  emit("update:modelValue", newDate);
  displayDate.value = QuasarDate.formatDate(newDate, displayFormat);
};
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}
</style>
