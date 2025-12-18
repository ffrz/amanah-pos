@php
  use App\Models\Setting;
  $is_pdf_export = isset($pdf) && $pdf;

  $logo_path = Setting::value('company.logo_path');
  if ($logo_path) {
      $logo_path = $is_pdf_export ? public_path($logo_path) : url($logo_path);
  }
  $title = '#' . $item->code;
  $foot_note = Setting::value('pos.foot_note');
@endphp

@extends('modules.admin.layouts.print-receipt-a4')

@section('content')
  @if (!$is_pdf_export)
    <div class="no-print text-center" style="margin-top: 10px; margin-bottom:20px;">
      <a class="btn my-xs" href="{{ route('admin.sales-order.add') }}">+ Order Baru</a>
      <a class="btn my-xs" href="{{ route('admin.sales-order.index') }}">&laquo; Daftar Order</a>
      <a class="btn my-xs" href="{{ route('admin.sales-order.detail', $item->id) }}">&laquo; Rincian</a>
      <a class="btn my-xs" href="{{ route('admin.sales-order.print', $item->id) }}?output=pdf&size=a4">Simpan PDF</a>
      <a class="btn my-xs" href="#" onclick="window.print()">Cetak</a>
    </div>
  @endif
  <div class="page">

    <x-admin.company-header :logo-path="$logo_path">
      <h4 style="margin: 0 0 5px 0; text-align: Left;">SALES INVOICE - {{ $item->code }}</h4>
      <table>
        <tr>
          <td>Waktu</td>
          <td>:</td>
          <td>{{ format_datetime($item->datetime) }}</td>
        </tr>
        <tr>
          <td>Status</td>
          <td>:</td>
          <td>{{ \App\Models\SalesOrder::Statuses[$item->status] }}</td>
        </tr>
        @if ($item->customer_id)
          <tr>
            <td style="vertical-align:top;">Pelanggan</td>
            <td style="vertical-align:top">:</td>
            <td>
              {{ $item->customer_code }}<br />
              {{ $item->customer_name }}
            </td>
          </tr>
          @if ($item->customer_phone)
            <tr>
              <td>No. Telepon</td>
              <td>:</td>
              <td>{{ $item->customer_phone }}</td>
            </tr>
          @endif
          <tr>
            <td style="vertical-align:top;">Alamat</td>
            <td>:</td>
            <td>{{ $item->customer_address }}</td>
          </tr>
        @else
          <tr>
            <td>Pelanggan</td>
            <td>:</td>
            <td>[Umum]</td>
          </tr>
        @endif
      </table>
    </x-admin.company-header>
    <br>
    <table class="table table-bordered table-striped table-condensed center-th table-sm" style="width:100%">
      <thead>
        <th style="width:1%">No</th>
        <th>Produk</th>
        <th style="width:3cm">Qty</th>
        <th style="width:3cm">Harga (Rp)</th>
        <th style="width:3cm">Subtotal (Rp)</th>
      </thead>
      <tbody>
        @php
          $total_cost = 0;
          $total_price = 0;
        @endphp
        @foreach ($item->details as $i => $detail)
          @php
            $subtotal_cost = $detail->cost * $detail->quantity;
            $subtotal_price = $detail->price * $detail->quantity;
            $total_cost += $subtotal_cost;
            $total_price += $subtotal_price;
          @endphp
          <tr>
            <td class="text-right">{{ $i + 1 }}</td>
            <td>{{ $detail->product->name }}</td>
            <td class="text-right">{{ format_number(abs($detail->quantity)) }} {{ $detail->product_uom }}
            </td>
            <td class="text-right">{{ format_number($detail->price) }}</td>
            <td class="text-right">{{ format_number(abs($subtotal_price)) }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <th class="text-right" colspan="4">Total</th>
          <th class="text-right">Rp. {{ format_number(abs($item->total_price)) }}</th>
        </tr>
        <tr>
          <th class="text-right" colspan="4">Diskon Akhir</th>
          <th class="text-right">Rp. {{ format_number(-$item->total_discount) }}</th>
        </tr>
        {{-- <tr>
                <th colspan="4" class="text-right">Diskon</th>
                <th class="text-right">{{ format_number(abs($item->total_discount)) }}</th>
            </tr>
            <tr>
                <th colspan="4" class="text-right">Pajak</th>
                <th class="text-right">{{ format_number(abs($item->total_tax)) }}</th>
            </tr>
            <tr>
                <th colspan="4" class="text-right">Biaya Lain</th>
                <th class="text-right">{{ format_number(abs($item->extra_payment)) }}</th>
            </tr> --}}
        <tr>
          <th class="text-right" colspan="4">Grand Total</th>
          <th class="text-right">Rp. {{ format_number(abs($item->grand_total)) }}</th>
        </tr>
      </tfoot>
    </table>
    <br>
    <table style="width:100%;">
      <tr>
        <td style="font-size: smaller;">
          @if ($foot_note)
            <div class="warning-notes">
              Catatan:<br>
              {!! $foot_note !!}
            </div>
          @endif
          @if ($item->cashierSession)
            <div>
              L: {{ $item->cashierSession->cashierTerminal?->location }} |
              T: {{ $item->cashierSession->cashierTerminal?->name }} | K:
              {{ $item->cashier->name }}
            </div>
          @endif
          <div>
            Dicetak oleh <b>{{ Auth::user()->username }}</b> pada {{ format_datetime(now()) }} |
            {{ config('app.name') . ' v' . config('app.version_str') }}
          </div>
        </td>
        <td style="width:4cm;text-align:center;">
          Hormat Kami,<br><br><br><br>
          {{ Auth::user()->name }}
        </td>
      </tr>
    </table>
  </div>
@endSection
