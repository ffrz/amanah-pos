@php
  use App\Models\Setting;

  $is_pdf_export = isset($pdf) && $pdf;

  $logo_path = Setting::value('company.logo_path');
  if ($logo_path) {
      $logo_path = $is_pdf_export ? public_path($logo_path) : url($logo_path);
  }

  $headers = [];
  foreach ($columns as $col_key) {
      if (isset($all_columns[$col_key])) {
          $headers[$col_key] = $all_columns[$col_key];
      }
  }

  $layout_extension = 'modules.admin.layouts.print-a4-' . $orientation;
@endphp

@extends($layout_extension)

@section('content')
  <div class="page">

    <x-admin.report.header :logo-path="$logo_path" :title="$title">
      <table class="report-header-info">
        <tr>
          <td style="width: 2cm;">Status</td>
          <td>:</td>
          <td>
            {{ $filter['status'] ? ($filter['status'] == 'all' ? 'Semua' : ($filter['status'] == 'active' ? 'Aktif' : 'Tidak Aktif')) : 'Semua' }}
          </td>
        </tr>
      </table>
    </x-admin.report.header>
    <table class="table table-bordered table-striped table-condensed center-th table-sm" style="width:100%">
      <thead>
        <tr>
          <th style="width:1%">No</th>
          @foreach ($headers as $col_key => $col_label)
            <th>{{ $col_label }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @forelse ($items as $i => $item)
          <tr>
            <td class="text-right">{{ $i + 1 }}</td>
            @foreach ($headers as $col_key => $col_label)
              <td
                class="
                                    {{ $col_key == 'balance' || $col_key == 'wallet_balance' ? 'text-right' : ($col_key == 'active' ? 'text-center' : 'text-left') }}">
                @if ($col_key == 'balance' || $col_key == 'wallet_balance')
                  {{ format_number($item->$col_key) }}
                @elseif ($col_key == 'active')
                  {{ $item->$col_key ? 'Aktif' : 'Tidak Aktif' }}
                @else
                  {{ $item->$col_key }}
                @endif
              </td>
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
