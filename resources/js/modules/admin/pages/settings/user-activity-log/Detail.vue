<script setup>
import { computed } from "vue";
import { formatDateTime } from "@/helpers/formatter";
import { router, usePage } from "@inertiajs/vue3";
import { handleDelete } from "@/helpers/client-req-handler";

const page = usePage();
const title = "Rincian Log Aktifitas";

const formattedMetadata = computed(() => {
  const metadataString = page.props.data.metadata;
  if (!metadataString) {
    return "Tidak ada Metadata.";
  }

  try {
    const jsonObject = JSON.parse(metadataString);
    return JSON.stringify(jsonObject, null, 2);
  } catch (e) {
    console.error("Gagal memformat JSON Metadata:", e);
    return metadataString;
  }
});

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
          @click="$goBack()"
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
            <q-card-section>
              <div class="text-subtitle1 text-bold text-grey-8">
                Info Log Aktifitas Pengguna
              </div>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width: 100px">ID</td>
                    <td style="width: 1px">:</td>
                    <td>{{ page.props.data.id }}</td>
                  </tr>
                  <tr>
                    <td>Waktu</td>
                    <td>:</td>
                    <td>{{ formatDateTime(page.props.data.logged_at) }}</td>
                  </tr>
                  <tr>
                    <td>Pengguna</td>
                    <td>:</td>
                    <td>
                      <i-link
                        :href="
                          route('admin.user.detail', {
                            id: page.props.data.user_id,
                          })
                        "
                      >
                        {{ page.props.data.username }}
                      </i-link>
                    </td>
                  </tr>
                  <tr>
                    <td>Kategori</td>
                    <td>:</td>
                    <td>{{ page.props.data.activity_category_label }}</td>
                  </tr>
                  <tr>
                    <td>Aktifitas</td>
                    <td>:</td>
                    <td>{{ page.props.data.activity_name_label }}</td>
                  </tr>
                  <tr>
                    <td>Deskripsi</td>
                    <td>:</td>
                    <td>{{ page.props.data.description }}</td>
                  </tr>
                  <tr>
                    <td>Alamat IP</td>
                    <td>:</td>
                    <td>{{ page.props.data.ip_address }}</td>
                  </tr>
                  <tr>
                    <td>User Agent</td>
                    <td>:</td>
                    <td style="text-wrap: initial !important">
                      {{ page.props.data.user_agent }}
                    </td>
                  </tr>
                  <tr>
                    <td>Meta Data</td>
                    <td>:</td>
                    <td>
                      <template
                        v-if="
                          !page.props.data.metadata ||
                          Object.keys(page.props.data.metadata).length === 0
                        "
                      >
                        <div class="text-italic text-grey-8">
                          Tidak tersedia
                        </div>
                      </template>
                    </td>
                  </tr>
                  <tr
                    v-if="
                      (typeof page.props.data.metadata === 'object' &&
                        Object.keys(page.props.data.metadata).length > 0) ||
                      (Array.isArray(page.props.data.metadata) &&
                        page.props.data.metadata.length > 0)
                    "
                  >
                    <td colspan="100%" class="q-pa-none bg-white">
                      <pre
                        class="bg-grey-3 q-ma-none"
                        style="overflow: auto; width: 100%; text-wrap: auto"
                      ><code class="text-caption text-mono">{{ formattedMetadata }}</code></pre>
                    </td>
                  </tr>
                </tbody>
              </table>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </q-page>
  </authenticated-layout>
</template>

<style scoped>
.detail td {
  padding-top: 4px;
  padding-bottom: 4px;
  vertical-align: top;
}

.text-mono {
  font-family: monospace;
}
</style>
