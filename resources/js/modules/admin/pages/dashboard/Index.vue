<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { getQueryParams } from "@/helpers/utils";
import ActualSummaryCard from "./cards/ActualSummaryCard.vue";
import ChartCard from "./cards/ChartCard.vue";
import TopCard from "./cards/TopCard.vue";

const title = "Dashboard";
const showFilter = ref(true);
const selected_period = ref(getQueryParams()["period"] ?? "this_month");
const period_options = ref([
  { value: "today", label: "Hari Ini" },
  { value: "yesterday", label: "Kemarin" },
  { value: "this_week", label: "Minggu Ini" },
  { value: "last_week", label: "Minggu Lalu" },
  { value: "this_month", label: "Bulan Ini" },
  { value: "prev_month", label: "Bulan Lalu" },
  { value: "prev_2month", label: "2 Bulan Sebelumnya" },
  { value: "prev_3month", label: "3 Bulan Sebelumnya" },
  { value: "this_year", label: "Tahun Ini" },
  { value: "prev_year", label: "Tahun Lalu" },
]);

const onFilterChange = () => {
  router.visit(route("admin.dashboard", { period: selected_period.value }));
};

const isDailiyPeriod = computed(() => {
  if (selected_period.value == 'today' ||
    selected_period.value == 'yesterday'
  ) {
    return false;
  }
  return true;
});
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <q-btn
        class="q-ml-sm"
        :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'"
        color="grey"
        dense
        rounded
        flat
        @click="showFilter = !showFilter"
      />
    </template>
    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <q-select
            class="custom-select col-12"
            style="min-width: 150px"
            v-model="selected_period"
            :options="period_options"
            label="Periode"
            dense
            map-options
            emit-value
            outlined
            @update:model-value="onFilterChange"
          />
        </div>
      </q-toolbar>
    </template>

    <div class="q-pa-xs">
      <div class="q-pa-none">
        <ActualSummaryCard />
      </div>
      <template v-if="isDailiyPeriod" >
      <ChartCard/>
      <TopCard />
      </template v-if>
    </div>
  </authenticated-layout>
</template>
