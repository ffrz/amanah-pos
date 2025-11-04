@php
  use App\Models\Setting;

  $is_pdf_export = isset($pdf) && $pdf;

  $logo_path = Setting::value('company.logo_path');
  if ($logo_path) {
      $logo_path = $is_pdf_export ? public_path($logo_path) : url($logo_path);
  }

  $totals = [
      'total_item' => 0,
      'grand_total' => 0,
  ];

@endphp

@extends('modules.admin.layouts.print-a4-portrait')

@section('content')
  <div class="page">

    <x-admin.report.header :logo-path="$logo_path" :title="$title">
      <table class="report-header-info">
        <tr>
          <td style="width: 2cm;">Periode</td>
          <td>:</td>
          <td>
            {{ format_datetime($filter['start_date']) }} s/d <br />
            {{ format_datetime($filter['end_date']) }}
          </td>
        </tr>
      </table>
    </x-admin.report.header>

    <table class="table table-bordered table-striped table-condensed center-th table-sm" style="width:100%">
      <thead>
        <tr>
          <th class="text-left">No. Transaksi</th>
          <th class="text-left">Tanggal</th>
          <th class="text-left">Pelanggan</th>
          <th class="text-right">Total Item</th>
          <th class="text-right">Grand Total</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($items as $i => $item)
          @php
            $totals['total_item'] += $item->total_item;
            $totals['grand_total'] += $item->grand_total;
          @endphp
          <tr>
            <td class="text-left">{{ $item->code }}</td>
            <td class="text-left">{{ format_datetime($item->datetime) }}</td>
            <td class="text-left">
              @if ($item->customer_code)
                {{ $item->customer_code }} - {{ $item->customer_name }}
              @endif
            </td>
            <td class="text-right">{{ format_number($item->total_item) }}</td>
            <td class="text-right">{{ format_number($item->grand_total) }}</td>
          </tr>
        @empty
          <tr>
            <td class="text-center" colspan="100%">
              Data transaksi tidak tersedia dalam periode ini.
            </td>
          </tr>
        @endforelse
      </tbody>
      @if (count($items) > 0)
        <tfoot>
          <tr class="font-weight-bold" style="background-color: #f0f0f0;">
            <th class="text-right" colspan="3">TOTAL</th>
            <th class="text-right">
              {{ format_number($totals['total_item']) }}
            </th>
            <th class="text-right">
              {{ format_number($totals['grand_total']) }}
            </th>
          </tr>
        </tfoot>
      @endif
    </table>
  </div>
@endSection
