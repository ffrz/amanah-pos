<script setup></script>

<template>
  <i-head title="Informasi Sistem" />
  <authenticated-layout>
    <template #title>Informasi Sistem</template>
    <div class="row">
      <q-card class="col q-ma-xs" square flat bordered>
        <q-card-section>
          <div class="q-mb-md text-h6">Tentang {{ $config.APP_NAME }}</div>
          Versi : {{ $config.APP_VERSION_STR }}<br />
          Dikembangkan oleh Shiftech Indonesia<br />
          CV. Shift Karya Teknologi
        </q-card-section>
      </q-card>
    </div>
  </authenticated-layout>
</template>
