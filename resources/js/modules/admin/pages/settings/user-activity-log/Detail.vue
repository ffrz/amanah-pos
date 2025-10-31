<script setup>
import { computed } from "vue";
import { formatDateTime } from "@/helpers/formatter";
import { router, usePage } from "@inertiajs/vue3";
import { handleDelete } from "@/helpers/client-req-handler";

const page = usePage();
const title = "Rincian Log Aktifitas";

const deleteItem = () =>
  handleDelete({
    message: `Hapus log aktifitas #${page.props.data.id}?`,
    url: route("admin.user-activity-log.delete", page.props.data.id),
    onSuccess: () => {
      router.get(route("admin.user-activity-log.index"));
    },
  });
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout :show-drawer-button="true">
    <template #title>{{ title }}</template>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="router.get(route('admin.user-activity-log.index'))"
        />
      </div>
    </template>

    <template #right-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="delete"
          dense
          color="grey"
          size="sm"
          rounded
          flat
          @click="deleteItem()"
        />
      </div>
    </template>

    <q-page class="row justify-center">
      <div class="col col-md-6 q-pa-xs">
        <div class="row">
          <q-card square flat bordered class="col">
            <q-card-section class="detail-group">
              <div class="text-subtitle1 text-bold text-grey-8 q-mb-sm">
                Info Log Aktifitas Pengguna
              </div>

              <div class="detail-item">
                <div class="detail-label">ID</div>

                <div class="detail-separator">:</div>

                <div class="detail-value">{{ page.props.data.id }}</div>
              </div>

              <div class="detail-item">
                <div class="detail-label">Waktu</div>

                <div class="detail-separator">:</div>

                <div class="detail-value">
                  {{ formatDateTime(page.props.data.logged_at) }}
                </div>
              </div>

              <div class="detail-item">
                <div class="detail-label">Pengguna</div>

                <div class="detail-separator">:</div>

                <div class="detail-value">
                  <i-link
                    :href="
                      route('admin.user.detail', {
                        id: page.props.data.user_id,
                      })
                    "
                  >
                    {{ page.props.data.username }}
                  </i-link>
                </div>
              </div>

              <div class="detail-item">
                <div class="detail-label">Kategori</div>

                <div class="detail-separator">:</div>

                <div class="detail-value">
                  {{ page.props.data.activity_category_label }}
                </div>
              </div>

              <div class="detail-item">
                <div class="detail-label">Aktifitas</div>

                <div class="detail-separator">:</div>

                <div class="detail-value">
                  {{ page.props.data.activity_name_label }}
                </div>
              </div>

              <div class="detail-item">
                <div class="detail-label">Deskripsi</div>

                <div class="detail-separator">:</div>

                <div class="detail-value">
                  {{ page.props.data.description }}
                </div>
              </div>

              <div class="detail-item">
                <div class="detail-label">Alamat IP</div>

                <div class="detail-separator">:</div>

                <div class="detail-value">
                  {{ page.props.data.ip_address }}
                </div>
              </div>

              <div class="detail-item">
                <div class="detail-label">User Agent</div>

                <div class="detail-separator">:</div>

                <div class="detail-value user-agent-value">
                  {{ page.props.data.user_agent }}
                </div>
              </div>

              <div class="q-pt-md">
                <div class="q-my-sm text-bold text-grey-8">Meta Data:</div>

                <div class="table-wrapper-scroll">
                  <template
                    v-if="Object.keys(page.props.data.metadata).length > 0"
                  >
                    <table class="table-metadata">
                      <tbody>
                        <template
                          v-for="(item, index) in page.props.formatted_metadata"
                          :key="index"
                        >
                          <tr v-if="item.type === 'plain'">
                            <td colspan="100%">
                              <pre class="text-mono json-pre-scroll">{{
                                item.value
                              }}</pre>
                            </td>
                          </tr>

                          <tr v-if="item.type === 'simple'">
                            <td class="metadata-label" style="width: 100px">
                              {{ item.label }}
                            </td>

                            <td class="metadata-value">
                              <template
                                v-if="item.label == 'Data Mentah (JSON)'"
                              >
                                <pre class="text-mono json-pre-scroll">{{
                                  item.value
                                }}</pre>
                              </template>

                              <template v-else>
                                {{ item.value }}
                              </template>
                            </td>
                          </tr>

                          <tr v-if="item.type === 'comparison'">
                            <td colspan="3">
                              <table class="table-metadata">
                                <thead>
                                  <tr>
                                    <td
                                      class="metadata-header"
                                      style="width: 100px"
                                    >
                                      Bidang
                                    </td>

                                    <td class="metadata-header">Nilai Lama</td>

                                    <td class="metadata-header">Nilai Baru</td>
                                  </tr>
                                </thead>

                                <tbody>
                                  <template
                                    v-for="(subItem, subIndex) in item.value"
                                  >
                                    <tr
                                      :class="
                                        subItem.new_value !== subItem.old_value
                                          ? 'value-changed'
                                          : ''
                                      "
                                    >
                                      <td class="metadata-label">
                                        {{ subItem.field }}
                                      </td>

                                      <td class="metadata-value">
                                        {{ subItem.old_value }}
                                      </td>

                                      <td class="metadata-value">
                                        {{ subItem.new_value }}
                                      </td>
                                    </tr>
                                  </template>
                                </tbody>
                              </table>
                            </td>
                          </tr>
                        </template>
                      </tbody>
                    </table>
                  </template>

                  <template v-else>
                    <div class="text-grey-8 text-italic q-pa-sm">
                      Tidak tersedia
                    </div>
                  </template>
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </q-page>
  </authenticated-layout>
</template>

<style scoped>
/* ---------------------------------------------------- */
/* 1. CSS UTAMA (Mengganti Table Detail dengan Flexbox) */
/* ---------------------------------------------------- */

/* Kontainer untuk semua detail log (Mengganti table.detail) */
.detail-group {
  padding: 16px; /* Atur padding kartu */
}

/* Setiap baris item detail (Mengganti tr) */
.detail-item {
  display: flex;
  padding-top: 4px;
  padding-bottom: 4px;
  align-items: flex-start; /* Label dan value rata atas */
}

/* Kolom Label (Mengganti td style="width: 100px") */
.detail-label {
  font-weight: bold;
  width: 100px; /* Lebar tetap, mencegah label menyusut */
  flex-shrink: 0;
}

/* Pemisah (Mengganti td style="width: 1px") */
.detail-separator {
  margin: 0 4px;
  flex-shrink: 0;
}

/* Kolom Nilai (Mengganti td konten) */
.detail-value {
  flex-grow: 1; /* Mengambil sisa lebar */
  word-break: break-word; /* Memastikan teks panjang biasa (deskripsi, link) membungkus */
  min-width: 0; /* KRITIS: Memungkinkan flex item menyusut agar tidak meluap */
}

/* ---------------------------------------------------- */
/* 2. CSS untuk Scroll JSON dan Tabel Metadata (Masih Menggunakan Table) */
/* ---------------------------------------------------- */

/* Wrapper untuk Scroll Horizontal di Meta Data (Karena masih berupa <table>) */
.table-wrapper-scroll {
  overflow-x: auto;
  width: 100%;
}

/* Elemen PRE yang berisi JSON */
.json-pre-scroll {
  white-space: pre; /* PALING KRITIS: Mencegah browser membungkus teks JSON */
  overflow-x: auto; /* Memastikan scrollbar muncul di dalam PRE itu sendiri */
  font-family: monospace;
  max-width: 100%;
  display: block;
  margin: 0;
  padding: 5px;
  box-sizing: border-box;
  background: #eee;
}

/* Tabel Metadata (Min-width untuk memaksa scroll) */
.table-metadata {
  width: 100%;
  border-collapse: collapse;
  min-width: 500px; /* Lebar minimum agar scroll muncul di mobile */
}

.metadata-header {
  text-align: center;
  background-color: #f9f9f9;
  font-weight: bold;
  padding: 0px 5px;
  border: 1px solid #ccc;
  color: #666;
}

.metadata-label,
.metadata-value {
  padding: 0px 5px;
  border: 1px solid #ccc;
  color: #666;
  vertical-align: top;
}

.metadata-label {
  font-weight: bold;
  color: #666;
}

/* Text Mono (untuk konsistensi) */
.text-mono {
  font-family: monospace;
}

.detail-value {
  flex-grow: 1;
  min-width: 0; /* KRITIS: Memungkinkan flex item menyusut */

  /* Tiga Properti Kritis untuk Memecahkan Kata Panjang Tanpa Spasi */
  word-wrap: break-word; /* Properti warisan (legacy) */
  overflow-wrap: break-word; /* Properti standar modern */
  word-break: break-all; /* PALING EFEKTIF: Memaksa pemotongan kata di mana saja */
}

.value-changed {
  background: #ff8;
}
.value-changed .metadata-value {
  color: #222;
}
</style>
