<script setup>
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import { computed, onMounted, onUnmounted } from "vue";

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true,
  },
  item: {
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

const subtotal = computed(() => {
  return props.item.quantity * props.item.cost;
});

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

const onBeforeShow = () => {
  // alert("show");
};
</script>
<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="(val) => $emit('update:modelValue', val)"
    @before-show="onBeforeShow"
  >
    <q-card>
      <q-card-section class="q-py-sm">
        <div class="row items-center no-wrap">
          <div class="col text-subtite text-bold text-grey-8">Edit Item</div>
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
        <q-input
          v-model="item.product_name"
          label="Produk"
          hide-bottom-space
          readonly
          :disable="isProcessing"
        />
        <LocaleNumberInput
          v-model="item.quantity"
          label="Kwantitas"
          hide-bottom-space
          autofocus
          :disable="isProcessing"
        />
        <LocaleNumberInput
          v-model="item.cost"
          label="Modal / Harga Beli"
          hide-bottom-space
          :disable="isProcessing"
        />
        <LocaleNumberInput
          v-model="subtotal"
          label="Subtotal"
          hide-bottom-space
          readonly
        />

        <q-input
          v-model="item.notes"
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
          :disable="isProcessing"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
