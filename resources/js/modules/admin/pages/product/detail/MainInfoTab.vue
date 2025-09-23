<script setup>
import { formatNumber, formatDateTime } from "@/helpers/formatter";
import { useQuasar } from "quasar";

const props = defineProps({
  product: {
    type: Object,
    required: true,
  },
});

const $q = useQuasar();

const marginInfo = (price) => {
  const val =
    price > 0
      ? formatNumber(((price - props.product.cost) / price) * 100, 2)
      : 0;
  return `${val}%`;
};
</script>

<template>
  <div>
    <div class="text-subtitle1 text-bold text-grey-9">Info Produk</div>
    <table class="detail">
      <tbody>
        <tr>
          <td style="width: 120px">ID</td>
          <td style="width: 1px">:</td>
          <td># {{ product.id }}</td>
        </tr>
        <tr>
          <td>Nama Produk</td>
          <td>:</td>
          <td>
            {{ product.name }}
          </td>
        </tr>
        <tr>
          <td>Barcode</td>
          <td>:</td>
          <td>
            {{ product.barcode }}
          </td>
        </tr>
        <tr>
          <td>Jenis Produk</td>
          <td>:</td>
          <td>
            {{ $CONSTANTS.PRODUCT_TYPES[product.type] }}
          </td>
        </tr>
        <tr>
          <td>Kategori</td>
          <td>:</td>
          <td>
            {{
              product.category
                ? product.category.name
                : "--Tidak memiliki kategori--"
            }}
          </td>
        </tr>
        <tr v-if="$can('admin.product:view-supplier')">
          <td>Supplier</td>
          <td>:</td>
          <td>
            <template v-if="product.supplier">
              <i-link
                :href="
                  route('admin.supplier.detail', {
                    id: product.supplier.id,
                  })
                "
              >
                {{ "#" + product.supplier.id + " - " + product.supplier.name }}
              </i-link>
            </template>
            <template v-else>
              {{ "--Tidak memiliki supplier--" }}
            </template>
          </td>
        </tr>
        <tr>
          <td>Status</td>
          <td>:</td>
          <td>
            {{ product.active ? "Aktif" : "Tidak Aktif" }}
          </td>
        </tr>
        <tr v-if="!!product.created_at">
          <td>Dibuat Oleh</td>
          <td>:</td>
          <td>
            <template v-if="product.creator">
              <i-link
                :href="
                  route('admin.user.detail', {
                    id: product.creator,
                  })
                "
              >
                {{ product.creator.username }}
              </i-link>
              -
            </template>
            {{ formatDateTime(product.created_at) }}
          </td>
        </tr>
        <tr v-if="!!product.updater">
          <td>Diperbarui oleh</td>
          <td>:</td>
          <td>
            <template v-if="product.updater">
              <i-link
                :href="
                  route('admin.user.detail', {
                    id: product.updater,
                  })
                "
              >
                {{ product.updater.username }}
              </i-link>
              -
            </template>
            {{ formatDateTime(product.updated_at) }}
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
          <td style="width: 120px">Stok</td>
          <td style="width: 1px">:</td>
          <td>
            {{ formatNumber(product.stock) }}
            {{ product.uom }}
          </td>
        </tr>
        <tr>
          <td>Stok Minimum</td>
          <td>:</td>
          <td>
            {{ formatNumber(product.min_stock) }}
            {{ product.uom }}
          </td>
        </tr>
        <tr>
          <td>Stok Maksimum</td>
          <td>:</td>
          <td>
            {{ formatNumber(product.max_stock) }}
            {{ product.uom }}
          </td>
        </tr>
        <tr>
          <td>Barcode</td>
          <td>:</td>
          <td>{{ product.barcode }}</td>
        </tr>
      </tbody>
    </table>
    <div class="text-subtitle1 q-pt-md text-bold text-grey-9">Info Harga</div>
    <table class="detail">
      <tbody>
        <tr>
          <td>Opsi Harga</td>
          <td>:</td>
          <td>
            {{ product.price_editable ? "Dapat" : "Tidak dapat" }}
            diubah saat penjualan.
          </td>
        </tr>
        <tr v-if="$can('admin.product:view-cost')">
          <td style="width: 120px">Harga Beli</td>
          <td style="width: 1px">:</td>
          <td>Rp. {{ formatNumber(product.cost) }}</td>
        </tr>
        <tr>
          <td>Harga Eceran</td>
          <td>:</td>
          <td>
            Rp. {{ formatNumber(product.price) }}
            <span v-if="$can('admin.product:view-cost')">
              ({{ marginInfo(product.price) }})
            </span>
          </td>
        </tr>
        <tr>
          <td>Harga Partai</td>
          <td>:</td>
          <td>
            Rp. {{ formatNumber(product.price_2) }}
            <span v-if="$can('admin.product:view-cost')">
              ({{ marginInfo(product.price_2) }})
            </span>
          </td>
        </tr>
        <tr>
          <td>Harga Grosir</td>
          <td>:</td>
          <td>
            Rp. {{ formatNumber(product.price_3) }}
            <span v-if="$can('admin.product:view-cost')">
              ({{ marginInfo(product.price_3) }})
            </span>
          </td>
        </tr>
      </tbody>
    </table>
    <div class="text-subtitle1 q-pt-md text-bold text-grey-9">
      Info Deskripsi & Catatan
    </div>
    <table class="detail">
      <tbody>
        <tr>
          <td style="width: 120px">Deskirpsi</td>
          <td style="width: 1px">:</td>
          <td>{{ product.description }}</td>
        </tr>
        <tr>
          <td>Catatan</td>
          <td>:</td>
          <td>{{ product.notes }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
