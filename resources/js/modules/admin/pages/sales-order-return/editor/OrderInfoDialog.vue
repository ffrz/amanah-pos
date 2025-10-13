<script setup>
import DateTimePicker from "@/components/DateTimePicker.vue";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import { computed, onMounted, onUnmounted } from "vue";

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true,
  },
  data: {
    type: Object,
    required: false,
  },
  isProcessing: {
    type: Boolean,
    required: false,
  },
});

const emit = defineEmits(["update:modelValue", "save"]);

const handleSave = () => {
  emit("save");
};

const preventEvent = (e) => {
  e.stopPropagation();
  e.preventDefault();
};

const handleKeyDown = (e) => {
  if (props.modelValue) {
    if (e.key === "Enter") {
      handleSave();
      preventEvent(e);
    } else if (e.key === "Escape") {
      emit("update:modelValue", false);
      preventEvent(e);
    }
  }
};

onMounted(() => {
  window.addEventListener("keydown", handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener("keydown", handleKeyDown);
});

const getCurrentItem = () => {
  return props.item;
};

defineExpose({
  getCurrentItem,
});
</script>
<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="(val) => $emit('update:modelValue', val)"
  >
    <q-card>
      <q-card-section class="q-py-sm">
        <div class="row items-center no-wrap">
          <div class="col text-subtite text-bold text-grey-8">
            Edit {{ data.formatted_id }}
          </div>
          <div class="col-auto">
            <q-btn
              flat
              size="sm"
              round
              icon="close"
              @click="$emit('update:modelValue', false)"
            />
          </div>
        </div>
      </q-card-section>

      <q-card-section class="q-py-sm">
        <DateTimePicker
          v-model="data.datetime"
          label="Waktu"
          hide-bottom-space
          :disable="isProcessing"
          readonly
          autofocus
        />

        <q-input
          v-model="$CONSTANTS.SALES_ORDER_STATUSES[data.status]"
          label="Status"
          hide-bottom-space
          readonly
          :disable="isProcessing"
        />

        <q-input
          v-model="data.notes"
          label="Catatan"
          autogrow
          type="textarea"
          counter
          maxlength="50"
          hide-bottom-space
          clearable
        />
      </q-card-section>
      <q-card-actions align="right">
        <q-btn
          flat
          label="Batal"
          color="primary"
          v-close-popup
          :disable="isProcessing"
        />
        <q-btn
          flat
          label="Simpan"
          color="primary"
          @click="handleSave"
          v-close-popup
          :disable="isProcessing"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
