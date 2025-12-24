<script setup>
import { ref, watch } from "vue";
import { useQuasar, copyToClipboard } from "quasar";

const props = defineProps({
  modelValue: Boolean,
  message: String,
  customers: Array, // Array dari form.customers (isi: {label, value, phone})
});

const emit = defineEmits(["update:modelValue"]);

const $q = useQuasar();
const localMessage = ref("");

watch(
  () => props.modelValue,
  (val) => {
    if (val) localMessage.value = props.message;
  }
);

const copyMessage = () => {
  copyToClipboard(localMessage.value).then(() => {
    $q.notify({
      message: "Pesan disalin!",
      color: "positive",
      icon: "content_copy",
      timeout: 1000,
    });
  });
};

const sendToWa = (phoneNumber) => {
  if (!phoneNumber) return;

  // Standarisasi nomor WA
  let cleanPhone = phoneNumber.replace(/\D/g, "");
  if (cleanPhone.startsWith("0")) {
    cleanPhone = "62" + cleanPhone.substring(1);
  }
  if (cleanPhone.startsWith("8")) {
    cleanPhone = "62" + cleanPhone;
  }

  const encodedMsg = encodeURIComponent(localMessage.value);
  window.open(`https://wa.me/${cleanPhone}?text=${encodedMsg}`, "_blank");
};
</script>

<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="emit('update:modelValue', $event)"
    persistent
  >
    <q-card style="min-width: 400px; width: 600px; max-width: 95vw">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-subtitle1 text-bold">Kirim Daftar Harga Manual</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section>
        <q-input
          v-model="localMessage"
          type="textarea"
          outlined
          autogrow
          label="Edit pesan final"
          input-style="font-family: monospace; font-size: 13px; min-height: 150px"
        />
      </q-card-section>

      <q-separator />

      <q-card-section
        class="q-pa-none"
        style="max-height: 250px; overflow-y: auto"
      >
        <q-list separator>
          <q-item v-for="c in customers" :key="c.value" dense class="q-py-sm">
            <q-item-section>
              <q-item-label class="text-bold">{{ c.label }}</q-item-label>
              <q-item-label caption :class="!c.phone ? 'text-red' : ''">
                {{ c.phone || "Nomor WA tidak tersedia" }}
              </q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-btn
                :label="c.phone ? 'Kirim' : 'No WA'"
                :color="c.phone ? 'green-8' : 'grey-5'"
                :icon="c.phone ? 'lab la-whatsapp' : 'warning'"
                :disable="!c.phone"
                size="sm"
                unelevated
                @click="sendToWa(c.phone)"
              />
            </q-item-section>
          </q-item>
        </q-list>
      </q-card-section>

      <q-card-actions align="right" class="q-pa-md">
        <q-btn
          flat
          label="Salin Teks"
          color="grey-8"
          icon="content_copy"
          @click="copyMessage"
        />
        <q-btn label="Tutup" color="primary" v-close-popup />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
