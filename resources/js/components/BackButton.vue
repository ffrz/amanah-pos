<template>
  <q-btn
    icon="arrow_back"
    dense
    color="grey-7"
    flat
    rounded
    @click="$inertia.get(props.route)"
  />
</template>
<script setup>
import { defineProps, defineEmits } from "vue";

const props = defineProps({
  route: {
    type: String,
    default: "",
    required: true,
  },
});
</script>
