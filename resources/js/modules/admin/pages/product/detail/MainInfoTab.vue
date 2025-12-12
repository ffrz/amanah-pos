<script setup>
import { handleDelete } from "@/helpers/client-req-handler";
import { formatNumber, formatDateTime } from "@/helpers/formatter";
import { useQuasar } from "quasar";
import { router } from "@inertiajs/vue3";

const props = defineProps({
  product: {
    type: Object,
    required: true,
  },
});

const $q = useQuasar();

const marginInfo = (price, cost) => {
  const val =
    price > 0 && cost > 0 ? formatNumber(((price - cost) / cost) * 100, 2) : 0;
  return `${val}%`;
};

// Helper untuk menghitung HPP Konversi di UI
const getUnitCost = (unit) => {
  if (unit.cost && parseFloat(unit.cost) > 0) return parseFloat(unit.cost);
  return parseFloat(props.product.cost) * parseFloat(unit.conversion_factor);
};

const confirmDelete = () => {
  handleDelete({
    message: `Hapus Produk ${props.product.name}?`,
    url: route("admin.product.delete", props.product.id),
    onSuccess: () => {
      router.get(route("admin.product.index"));
    },
  });
};

// LOGIC FALLBACK HARGA (Sama dengan Index.vue)
const getPriceDisplay = (p1, p2, p3) => {
  const price1 = parseFloat(p1) || 0;
  let price2 = parseFloat(p2) || 0;
  let price3 = parseFloat(p3) || 0;

  const isP2Fallback = price2 === 0;
  if (isP2Fallback) price2 = price1;

  const isP3Fallback = price3 === 0;
  if (isP3Fallback) price3 = price2;

  return {
    p1: { val: price1, isFallback: false },
    p2: { val: price2, isFallback: isP2Fallback },
    p3: { val: price3, isFallback: isP3Fallback },
  };
};
</script>

<template>
  <div>
    <div class="text-subtitle1 text-bold text-grey-9">Info Produk</div>
    <table class="detail">
      <tbody>
        <tr>
          <td style="width: 120px">Kode / Nama</td>
          <td style="width: 1px">:</td>
          <td>{{ product.name }}</td>
        </tr>
        <tr>
          <td>Deskirpsi</td>
          <td>:</td>
          <td>{{ product.description }}</td>
        </tr>
        <tr>
          <td>Barcode</td>
          <td>:</td>
          <td>{{ product.barcode }}</td>
        </tr>
        <tr>
          <td>Jenis Produk</td>
          <td>:</td>
          <td>{{ $CONSTANTS.PRODUCT_TYPES[product.type] }}</td>
        </tr>
        <tr>
          <td>Kategori</td>
          <td>:</td>
          <td>{{ product.category ? product.category.name : "--" }}</td>
        </tr>
        <tr v-if="$can('admin.product:view-supplier')">
          <td>Supplier</td>
          <td>:</td>
          <td>
            <template v-if="product.supplier">
              <i-link
                :href="
                  route('admin.supplier.detail', { id: product.supplier.id })
                "
              >
                {{ product.supplier.name }}
              </i-link>
            </template>
            <template v-else>--</template>
          </td>
        </tr>
        <tr>
          <td>Status</td>
          <td>:</td>
          <td>{{ product.active ? "Aktif" : "Tidak Aktif" }}</td>
        </tr>
        <tr v-if="!!product.created_at">
          <td>Dibuat</td>
          <td>:</td>
          <td>
            <span v-if="product.creator">{{ product.creator.name }}</span> -
            {{ formatDateTime(product.created_at) }}
          </td>
        </tr>
      </tbody>
    </table>

    <div class="text-subtitle1 q-pt-lg text-bold text-grey-9">
      Info Inventori
    </div>
    <table class="detail">
      <tbody>
        <tr>
          <td style="width: 120px">Stok Fisik</td>
          <td style="width: 1px">:</td>
          <td>
            <div class="text-weight-bold text-primary">
              {{ product.stock_breakdown }}
            </div>
            <div
              class="text-caption text-grey-7"
              v-if="product.product_units.length > 0"
            >
              (Total: {{ formatNumber(product.stock) }} {{ product.uom }})
            </div>
          </td>
        </tr>
        <tr>
          <td>Stok Min</td>
          <td>:</td>
          <td>{{ formatNumber(product.min_stock) }} {{ product.uom }}</td>
        </tr>
      </tbody>
    </table>

    <div class="text-subtitle1 q-pt-md text-bold text-grey-9">Info Harga</div>

    <div class="q-markup-table q-table--dense q-mt-sm">
      <table style="width: 100%; border-collapse: collapse">
        <thead>
          <tr class="text-left bg-grey-2">
            <th class="q-pa-sm" style="border-bottom: 1px solid #ddd">
              Satuan
            </th>
            <th
              v-if="$can('admin.product:view-cost')"
              class="q-pa-sm"
              style="border-bottom: 1px solid #ddd"
            >
              Modal
            </th>
            <th class="q-pa-sm" style="border-bottom: 1px solid #ddd">
              Eceran
            </th>
            <th class="q-pa-sm" style="border-bottom: 1px solid #ddd">
              Partai
            </th>
            <th class="q-pa-sm" style="border-bottom: 1px solid #ddd">
              Grosir
            </th>
          </tr>
        </thead>
        <tbody>
          <tr class="bg-blue-1">
            <td
              class="q-pa-sm text-bold text-primary"
              style="border-bottom: 1px solid #eee"
            >
              {{ product.uom }} (Dasar)
            </td>

            <td
              v-if="$can('admin.product:view-cost')"
              class="q-pa-sm text-bold"
              style="border-bottom: 1px solid #eee"
            >
              Rp {{ formatNumber(product.cost) }}
            </td>

            <template
              v-for="disp in [
                getPriceDisplay(
                  product.price_1,
                  product.price_2,
                  product.price_3
                ),
              ]"
            >
              <td
                class="q-pa-sm text-bold"
                style="border-bottom: 1px solid #eee"
              >
                <div>Rp {{ formatNumber(disp.p1.val) }}</div>
                <div
                  v-if="$can('admin.product:view-cost')"
                  class="text-caption text-green"
                >
                  {{ marginInfo(disp.p1.val, product.cost) }}
                </div>
              </td>

              <td
                class="q-pa-sm text-bold"
                style="border-bottom: 1px solid #eee"
              >
                <div
                  :class="disp.p2.isFallback ? 'text-grey-6 text-italic' : ''"
                >
                  Rp {{ formatNumber(disp.p2.val) }}
                  <q-tooltip v-if="disp.p2.isFallback"
                    >Harga belum diset, ikut eceran</q-tooltip
                  >
                </div>
                <div
                  v-if="$can('admin.product:view-cost')"
                  class="text-caption text-green"
                >
                  {{ marginInfo(disp.p2.val, product.cost) }}
                </div>
              </td>

              <td
                class="q-pa-sm text-bold"
                style="border-bottom: 1px solid #eee"
              >
                <div
                  :class="disp.p3.isFallback ? 'text-grey-6 text-italic' : ''"
                >
                  Rp {{ formatNumber(disp.p3.val) }}
                  <q-tooltip v-if="disp.p3.isFallback"
                    >Harga belum diset, ikut partai/eceran</q-tooltip
                  >
                </div>
                <div
                  v-if="$can('admin.product:view-cost')"
                  class="text-caption text-green"
                >
                  {{ marginInfo(disp.p3.val, product.cost) }}
                </div>
              </td>
            </template>
          </tr>

          <tr v-for="unit in product.product_units" :key="unit.id">
            <td class="q-pa-sm text-bold" style="border-bottom: 1px solid #eee">
              {{ unit.name }}
              <span class="text-caption text-grey font-weight-normal"
                >(x{{ formatNumber(unit.conversion_factor) }})</span
              >
            </td>

            <td
              v-if="$can('admin.product:view-cost')"
              class="q-pa-sm"
              style="border-bottom: 1px solid #eee"
            >
              Rp {{ formatNumber(getUnitCost(unit)) }}
            </td>

            <template
              v-for="uDisp in [
                getPriceDisplay(unit.price_1, unit.price_2, unit.price_3),
              ]"
            >
              <td class="q-pa-sm" style="border-bottom: 1px solid #eee">
                <div>Rp {{ formatNumber(uDisp.p1.val) }}</div>
                <div
                  v-if="$can('admin.product:view-cost')"
                  class="text-caption text-green"
                >
                  {{ marginInfo(uDisp.p1.val, getUnitCost(unit)) }}
                </div>
              </td>

              <td class="q-pa-sm" style="border-bottom: 1px solid #eee">
                <div
                  :class="uDisp.p2.isFallback ? 'text-grey-5 text-italic' : ''"
                >
                  Rp {{ formatNumber(uDisp.p2.val) }}
                </div>
                <div
                  v-if="$can('admin.product:view-cost')"
                  class="text-caption text-green"
                >
                  {{ marginInfo(uDisp.p2.val, getUnitCost(unit)) }}
                </div>
              </td>

              <td class="q-pa-sm" style="border-bottom: 1px solid #eee">
                <div
                  :class="uDisp.p3.isFallback ? 'text-grey-5 text-italic' : ''"
                >
                  Rp {{ formatNumber(uDisp.p3.val) }}
                </div>
                <div
                  v-if="$can('admin.product:view-cost')"
                  class="text-caption text-green"
                >
                  {{ marginInfo(uDisp.p3.val, getUnitCost(unit)) }}
                </div>
              </td>
            </template>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="text-subtitle1 q-pt-md text-bold text-grey-9">
      Info Deskripsi & Catatan
    </div>
    <table class="detail">
      <tbody>
        <tr>
          <td style="width: 120px">Catatan</td>
          <td style="width: 1px">:</td>
          <td>{{ product.notes || "-" }}</td>
        </tr>
      </tbody>
    </table>

    <div class="q-pt-md" v-if="$can('admin.product.delete')">
      <q-btn
        icon="delete"
        label="Hapus"
        color="negative"
        @click="confirmDelete()"
      />
    </div>
  </div>
</template>

<style scoped>
/* Sedikit styling tambahan untuk tabel custom */
.detail td {
  vertical-align: top;
  padding-bottom: 8px;
}
</style>
