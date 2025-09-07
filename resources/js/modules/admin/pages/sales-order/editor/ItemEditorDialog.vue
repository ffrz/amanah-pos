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
    required: true,
  },
});

const emit = defineEmits(["update:modelValue", "save"]);

const handleSave = () => {
  emit("save");
};

const subtotal = computed(() => {
  return props.item.quantity * props.item.price;
});

const preventEventAndClose = (e) => {
  emit("update:modelValue", false);
  e.stopPropagation();
  e.preventDefault();
};

const handleKeyDown = (e) => {
  if (props.modelValue) {
    if (e.key === "Enter") {
      handleSave();
      preventEventAndClose(e);
    } else if (e.key === "Escape") {
      preventEventAndClose(e);
    }
  }
};

onMounted(() => {
  window.addEventListener("keydown", handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener("keydown", handleKeyDown);
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
          <div class="col text-subtite text-bold text-grey-8">Edit Item</div>
          <div class="col-auto">
            <q-btn
              flat
              size="xs"
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
        />
        <LocaleNumberInput
          v-model="item.quantity"
          label="Kwantitas"
          hide-bottom-space
          autofocus
        />
        <LocaleNumberInput
          v-model="item.price"
          label="Harga"
          :readonly="!item.price_editable"
          hide-bottom-space
        />
        <LocaleNumberInput
          v-model="subtotal"
          label="Subtotal"
          hide-bottom-space
        />
      </q-card-section>
      <q-card-actions align="right">
        <q-btn flat label="Batal" color="primary" v-close-popup />
        <q-btn
          flat
          label="Simpan"
          color="primary"
          @click="handleSave"
          v-close-popup
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
