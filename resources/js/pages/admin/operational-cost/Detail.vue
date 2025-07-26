<script setup>
import { router, usePage } from "@inertiajs/vue3";

const page = usePage();
const title = "Rincian Teknisi";
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="$inertia.get(route('admin.operational-cost.index'))"
        />
      </div>
    </template>

    <template #right-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="edit"
          dense
          color="primary"
          @click="
            router.get(
              route('admin.operational-cost.edit', { id: page.props.data.id })
            )
          "
        />
      </div>
    </template>

    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <div class="row">
          <q-card square flat bordered class="col">
            <q-card-section>
              <div class="text-subtitle1 text-bold text-grey-8">
                Profil Teknisi
              </div>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width: 125px">Akun Pengguna</td>
                    <td style="width: 1px">:</td>
                    <td>
                      <template v-if="!!page.props.data.user">
                        <i-link
                          :href="
                            route('admin.user.detail', {
                              id: page.props.data.user.id,
                            })
                          "
                        >
                          {{ page.props.data.user.username }}
                        </i-link>
                      </template>
                      <template v-else>
                        <span class="text-grey-9">Tidak terhubung</span>
                      </template>
                    </td>
                  </tr>
                  <tr>
                    <td>Nama Teknisi</td>
                    <td>:</td>
                    <td>{{ page.props.data.name }}</td>
                  </tr>
                  <tr>
                    <td>No. Telepon</td>
                    <td>:</td>
                    <td>{{ page.props.data.phone }}</td>
                  </tr>
                  <tr>
                    <td>Email</td>
                    <td>:</td>
                    <td>{{ page.props.data.email }}</td>
                  </tr>
                  <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>{{ page.props.data.address }}</td>
                  </tr>
                </tbody>
              </table>
            </q-card-section>
            <q-card-section>
              <div class="text-subtitle1 text-bold text-grey-8">
                Statistik Servis
              </div>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width: 125px">Servis Ditangani</td>
                    <td style="width: 1px">:</td>
                    <td>0</td>
                  </tr>
                  <tr>
                    <td>Servis Sukses</td>
                    <td>:</td>
                    <td>0</td>
                  </tr>
                  <tr>
                    <td>Servis Gagal</td>
                    <td>:</td>
                    <td>0</td>
                  </tr>
                </tbody>
              </table>
            </q-card-section>
            <q-card-section>
              <div class="text-subtitle1 text-bold text-grey-8">
                Informasi Ekstra
              </div>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width: 125px">Dibuat</td>
                    <td style="width: 1px">:</td>
                    <td>
                      {{
                        $dayjs(new Date(page.props.data.created_at)).format(
                          "DD MMMM YY HH:mm:ss"
                        )
                      }}
                    </td>
                  </tr>
                  <tr>
                    <td>Diperbarui</td>
                    <td>:</td>
                    <td>
                      {{
                        $dayjs(new Date(page.props.data.updated_at)).format(
                          "DD MMMM YY HH:mm:ss"
                        )
                      }}
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
