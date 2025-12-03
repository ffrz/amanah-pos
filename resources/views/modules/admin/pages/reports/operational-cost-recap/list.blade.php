@php
  use App\Models\Setting;

  // Logic Logo standar
  $logo_path = Setting::value('company.logo_path');
  if ($logo_path) {
      $logo_path = isset($pdf) && $pdf ? public_path($logo_path) : url($logo_path);
  }

  // Hitung Grand Total untuk keperluan Persentase
  $grand_total = $items->sum('total_amount');
  $total_trx_count = $items->sum('total_trx');
@endphp

@extends('modules.admin.layouts.print-a4-portrait')

@section('content')
  <div class="page">
    {{-- Header --}}
    <x-admin.report.header :logo-path="$logo_path" :title="$title">
      <table class="report-header-info">
        <tr>
          <td style="width: 3cm;">Periode</td>
          <td>:</td>
          <td>{{ format_date($filter['start_date']) }} s/d {{ format_date($filter['end_date']) }}</td>
        </tr>
        @if (isset($filter['accounts']) && !empty(array_filter((array) $filter['accounts'])))
          <tr>
            <td>Akun</td>
            <td>:</td>
            <td>{{ count((array) $filter['accounts']) > 1 ? 'Beberapa Akun' : '1 Akun Terpilih' }}</td>
          </tr>
        @endif
      </table>
    </x-admin.report.header>

    {{-- Tabel Rekap --}}
    <table class="table table-bordered table-striped table-condensed center-th table-sm" style="width:100%">
      <thead>
        <tr>
          <th style="width: 5%">No</th>
          <th>Kategori Biaya</th>
          <th style="width: 15%">Frekuensi</th>
          <th style="width: 25%">Total Biaya (Rp)</th>
          <th style="width: 15%">Proporsi (%)</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($items as $i => $item)
          @php
            $percentage = $grand_total > 0 ? ($item->total_amount / $grand_total) * 100 : 0;
            // Handle jika category terhapus atau null
            $catName = $item->category->name ?? 'Tanpa Kategori / Lain-lain';
          @endphp
          <tr>
            <td class="text-right">{{ $i + 1 }}</td>
            <td class="text-left font-weight-bold">{{ $catName }}</td>
            <td class="text-center">{{ $item->total_trx }}</td>
            <td class="text-right">{{ format_number($item->total_amount) }}</td>
            <td class="text-right">{{ number_format($percentage, 2) }}%</td>
          </tr>
        @empty
          <tr>
            <td class="text-center" colspan="5">Tidak ada data biaya pada periode ini.</td>
          </tr>
        @endforelse
      </tbody>
      {{-- Footer Total --}}
      @if (count($items) > 0)
        <tfoot>
          <tr>
            <th class="text-right font-weight-bold" colspan="2">TOTAL KESELURUHAN</th>
            <th class="text-center font-weight-bold">{{ $total_trx_count }}</th>
            <th class="text-right font-weight-bold">{{ format_number($grand_total) }}</th>
            <th class="text-right font-weight-bold">100%</th>
          </tr>
        </tfoot>
      @endif
    </table>

    {{-- Visualisasi Bar Chart Sederhana (CSS only) --}}
    @if ($grand_total > 0)
      <div style="margin-top: 25px; page-break-inside: avoid;">
        <h5 style="margin-bottom: 10px; font-size: 14px; border-bottom: 1px solid #ccc; padding-bottom: 5px;">Visualisasi 5 Pengeluaran Terbesar</h5>
        <table style="width: 100%; border-collapse: collapse; font-size: 0.8em;">
          @foreach ($items->take(5) as $item)
            @php
              $width = ($item->total_amount / $grand_total) * 100;
              $catName = $item->category->name ?? 'Tanpa Kategori';
            @endphp
            <tr>
              <td style="width: 25%; padding: 4px 0; vertical-align: middle;">{{ $catName }}</td>
              <td style="width: 75%; padding: 4px 0; vertical-align: middle;">
                <div style="background-color: #f0f0f0; width: 100%; height: 18px; border-radius: 2px;">
                  <div
                    style="background-color: #555; width: {{ $width }}%; height: 100%; border-radius: 2px; line-height: 18px; color: #fff; padding-left: 5px; font-size: 10px;">
                    @if ($width > 10)
                      {{ format_number($item->total_amount) }}
                    @endif
                  </div>
                </div>
              </td>
            </tr>
          @endforeach
        </table>
      </div>
    @endif
  </div>
@endSection
