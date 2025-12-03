@php
  use App\Models\Setting;

  $is_pdf_export = isset($pdf) && $pdf;

  $logo_path = Setting::value('company.logo_path');
  if ($logo_path) {
      $logo_path = $is_pdf_export ? public_path($logo_path) : url($logo_path);
  }

  $total_amount = 0;
@endphp

@extends('modules.admin.layouts.print-a4-portrait')

@section('content')
  <div class="page">
    {{-- HEADER LAPORAN --}}
    <x-admin.report.header :logo-path="$logo_path" :title="$title">
      <table class="report-header-info">
        {{-- Periode Tanggal --}}
        <tr>
          <td style="width: 3cm;">Periode</td>
          <td>:</td>
          <td>
            {{ format_date($filter['start_date']) }} s/d {{ format_date($filter['end_date']) }}
          </td>
        </tr>

        {{-- Informasi Filter Akun --}}
        @if (isset($filter['accounts']) && !empty(array_filter((array) $filter['accounts'])))
          <tr>
            <td>Akun Keuangan</td>
            <td>:</td>
            <td>
              {{ count((array) $filter['accounts']) > 1 ? 'Beberapa Akun Dipilih' : '1 Akun Dipilih' }}
            </td>
          </tr>
        @endif

        {{-- Informasi Filter Kategori --}}
        @if (isset($filter['categories']) && !empty(array_filter((array) $filter['categories'])))
          @php
            $category_list = array_filter((array) $filter['categories']);
            $has_none = in_array('none', $category_list);
            $id_count = count(array_filter($category_list, 'is_numeric'));

            $category_text = '';
            if ($has_none && $id_count > 0) {
                $category_text = 'Tanpa Kategori & Kategori Lainnya';
            } elseif ($has_none) {
                $category_text = 'Tanpa Kategori';
            } elseif ($id_count > 1) {
                $category_text = 'Beberapa Kategori Dipilih';
            } elseif ($id_count == 1) {
                $category_text = '1 Kategori Dipilih';
            }
          @endphp
          @if (!empty($category_text))
            <tr>
              <td>Kategori</td>
              <td>:</td>
              <td>{{ $category_text }}</td>
            </tr>
          @endif
        @endif
      </table>
    </x-admin.report.header>

    {{-- DETAIL DATA --}}
    <table class="table table-bordered table-striped table-condensed center-th table-sm" style="width:100%">
      <thead>
        <tr>
          <th style="width: 1%">No</th>
          <th style="width: 10%">Tanggal</th>
          <th style="width: 10%">Kode</th>
          <th style="width: 15%">Akun</th>
          <th style="width: 15%">Kategori</th>
          <th>Keterangan</th>
          <th style="width: 15%">Jumlah (Rp)</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($items as $i => $item)
          @php
            $amount = $item->amount;
            $total_amount += $amount;
          @endphp
          <tr>
            <td class="text-right">{{ $i + 1 }}</td>
            <td class="text-center">{{ format_date($item->date, 'd-m-Y') }}</td>
            <td class="text-center">{{ $item->code }}</td>
            <td class="text-left">{{ $item->financeAccount->name ?? '-' }}</td>
            <td class="text-left">{{ $item->category->name ?? 'Tanpa Kategori' }}</td>
            <td class="text-left">
              {{-- Menampilkan Description, dan Notes jika ada --}}
              {{ $item->description }}
              @if ($item->notes)
                <br><small class="text-muted">Catatan: {{ $item->notes }}</small>
              @endif
            </td>
            <td class="text-right">
              {{ format_number($amount) }}
            </td>
          </tr>
        @empty
          <tr>
            <td class="text-center" colspan="7">Data biaya operasional tidak ditemukan pada periode ini.</td>
          </tr>
        @endforelse
      </tbody>
      {{-- BARIS TOTAL --}}
      @if (count($items) > 0)
        <tfoot>
          <tr>
            <th class="text-right" style="font-weight: bold;" colspan="6">
              TOTAL BIAYA OPERASIONAL
            </th>
            <th class="text-right" style="font-weight: bold;">
              {{ format_number($total_amount) }}
            </th>
          </tr>
        </tfoot>
      @endif
    </table>

    {{-- Footer Timestamp --}}
    <div style="margin-top: 10px; font-size: 0.8em; text-align: right; color: #555;">
      <i>Dicetak pada: {{ date('d-m-Y H:i') }} oleh {{ auth()->user()->name ?? 'System' }}</i>
    </div>
  </div>
@endSection
