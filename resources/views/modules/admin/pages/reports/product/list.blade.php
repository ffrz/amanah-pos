@php
  use App\Models\Setting;
  use App\Models\Product; // Import Model Product untuk mengakses konstanta Types
  use App\Models\ProductCategory; // Jika Anda perlu label Category
  use App\Models\Supplier; // Jika Anda perlu label Supplier

  // $price_types TIDAK DIPERLUKAN di sini karena ini adalah Product Report,
  // BUKAN Customer Report yang menunjukkan level harga default.

  $is_pdf_export = isset($pdf) && $pdf;

  $logo_path = Setting::value('company.logo_path');
  if ($logo_path) {
      $logo_path = $is_pdf_export ? public_path($logo_path) : url($logo_path);
  }

  // 💡 1. MAPPING KOLOM: Sesuaikan dengan kolom Produk dari Controller
  // Gabungkan primary_columns dan optional_columns yang didefinisikan di ProductReportController
  $all_columns_map = [
      'name' => 'Nama',
      'cost' => 'Modal / Harga Beli (Rp)', // Currency
      'price_1' => 'Harga Eceran (Rp)', // Currency
      'price_2' => 'Harga Partai (Rp)', // Currency
      'price_3' => 'Harga Grosir (Rp)', // Currency
      'stock' => 'Stok', // Numeric/Decimal
      'active' => 'Status', // Boolean
      'type' => 'Jenis', // Label/Constant
      'category' => 'Kategori', // Relation
      'brand' => 'Merk',
      'supplier' => 'Pemasok', // Relation
  ];

  $headers = [];
  foreach ($columns as $col_key) {
      // Ambil header hanya untuk kolom yang diminta (dari $data['columns'] di Controller)
      if (isset($all_columns_map[$col_key])) {
          $headers[$col_key] = $all_columns_map[$col_key];
      }
  }

  $layout_extension = 'modules.admin.layouts.print-a4-' . ($orientation ?? 'portrait'); // Tambahkan default orientation

  $format_filter_list = function ($filter_ids, $master_list, $id_key = 'id', $name_key = 'name', $limit = 3) {
      if (empty($filter_ids)) {
          return 'Semua';
      }

      // 1. Ambil nama-nama yang dipilih (Mapping)
      $selected_names = collect($master_list)->whereIn($id_key, $filter_ids)->pluck($name_key)->toArray();

      $count = count($selected_names);

      if ($count == 0) {
          return 'Semua (Data tidak ditemukan)';
      }

      // 2. Batasi tampilan
      $display_names = array_slice($selected_names, 0, $limit);
      $output = implode(', ', $display_names);

      // 3. Tambahkan "dan X lainnya" jika melebihi batas
      if ($count > $limit) {
          $remaining = $count - $limit;
          $output .= ", dan {$remaining} lainnya";
      }

      return $output;
  };
@endphp

@extends($layout_extension)

@section('content')
  <div class="page">
    <x-admin.report.header :logo-path="$logo_path" :title="$title">
      <table class="report-header-info">
        <tr>
          <td style="width: 3cm;">Status</td>
          <td style="vertical-align: top">:</td>
          <td>
            {{ $filter['status'] ? ($filter['status'] == 'all' ? 'Semua' : ($filter['status'] == 'active' ? 'Aktif' : 'Tidak Aktif')) : 'Semua' }}
          </td>
        </tr>

        @if (!empty($filter['types']))
          <tr>
            <td style="vertical-align: top">Jenis</td>
            <td style="vertical-align: top">:</td>
            <td>
              {{ implode(', ', array_map(fn($t) => Product::Types[$t] ?? $t, $filter['types'])) }}
            </td>
          </tr>
        @endif

        @if (!empty($filter['brands']))
          <tr>
            <td style="vertical-align: top">Merk</td>
            <td style="vertical-align: top">:</td>
            <td>
              {{ $format_filter_list($filter['brands'], $brands, 'id', 'name', 2) }}
            </td>
          </tr>
        @endif

        @if (!empty($filter['categories']))
          <tr>
            <td style="vertical-align: top">Kategori</td>
            <td style="vertical-align: top">:</td>
            <td>
              {{ $format_filter_list($filter['categories'], $categories, 'id', 'name', 2) }}
            </td>
          </tr>
        @endif

        @if (!empty($filter['suppliers']))
          <tr>
            <td style="vertical-align: top">Pemasok</td>
            <td style="vertical-align: top">:</td>
            <td>
              {{ $format_filter_list($filter['suppliers'], $suppliers, 'id', 'name', 2) }}
            </td>
          </tr>
        @endif
      </table>
    </x-admin.report.header>
    <table class="table table-bordered table-striped table-condensed center-th table-sm" style="width:100%">
      <thead>
        <tr>
          <th style="width:1%">No</th>
          @foreach ($headers as $col_key => $col_label)
            <th
              class="{{ match ($col_key) {
                  'cost', 'price_1', 'price_2', 'price_3' => 'text-right',
                  'active', 'type', 'stock' => 'text-center',
                  default => 'text-left',
              } }}"
              colspan="{{ match ($col_key) {
                  'stock' => '2',
                  default => '1',
              } }}">
              {{ $col_label }}
            </th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @forelse ($items as $i => $item)
          <tr>
            <td class="text-right">{{ $i + 1 }}</td>
            @foreach ($headers as $col_key => $col_label)
              @if ($col_key == 'stock')
                <td class="text-right">
                  {{ format_number($item->$col_key) }}
                </td>
                <td>
                  {{ $item->uom ?? '' }}
                </td>
              @else
                <td
                  class="{{ match ($col_key) {
                      'cost', 'price_1', 'price_2', 'price_3' => 'text-right',
                      'active', 'type' => 'text-center',
                      default => 'text-left',
                  } }}">
                  @if (in_array($col_key, ['cost', 'price_1', 'price_2', 'price_3']))
                    {{ format_number($item->$col_key) }}
                  @elseif ($col_key == 'active')
                    {{ $item->$col_key ? 'Aktif' : 'Tidak Aktif' }}
                  @elseif ($col_key == 'type')
                    {{ $item->type_label }}
                  @elseif ($col_key == 'category')
                    {{ $item->category->name ?? '-' }}
                  @elseif ($col_key == 'brand')
                    {{ $item->brand->name ?? '-' }}
                  @elseif ($col_key == 'supplier')
                    {{ $item->supplier->name ?? '-' }}
                  @else
                    {{ $item->$col_key }}
                  @endif
                </td>
              @endif
            @endforeach
          </tr>
        @empty
          <tr>
            <td class="text-center" colspan="{{ count($headers) + 1 }}">Data tidak tersedia.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endSection
