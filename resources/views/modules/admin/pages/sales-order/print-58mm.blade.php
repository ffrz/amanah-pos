@php
  use App\Models\Setting;
  $is_pdf_export = isset($pdf) && $pdf;
  $logo_path = Setting::value('company.logo_path');
  if ($logo_path) {
      $logo_path = $is_pdf_export ? public_path($logo_path) : url($logo_path);
  }
  $title = '#' . $item->code;
@endphp

@extends('modules.admin.layouts.print-receipt-58mm')

@section('content')
  @if (!$is_pdf_export)
    <div class="no-print text-center">
      <br>
      <a class="btn my-xs" href="{{ route('admin.sales-order.add') }}">+ Order Baru</a>
      <a class="btn my-xs" href="{{ route('admin.sales-order.index') }}">&laquo; Daftar Order</a>
      <a class="btn my-xs" href="{{ route('admin.sales-order.detail', $item->id) }}">&laquo; Rincian</a>
      <a class="btn my-xs" href="{{ route('admin.sales-order.print', $item->id) }}?output=pdf&size=58">Simpan PDF</a>
      <a class="btn my-xs" href="#" onclick="window.print()">Cetak</a>
      <br><br><br>
    </div>
  @endif
  <div class="page">
    <table style="width:100%">
      <tr>
        <td class="text-center">
          <div>
            <b>{{ Setting::value('company.name') }}</b>
          </div>

          <div style="font-size: smaller;">
            @if (!empty(Setting::value('company.headline')))
              <div>
                {{ Setting::value('company.headline') }}
              </div>
            @endif

            @if (!empty(Setting::value('company.address')))
              <div>
                {{ Setting::value('company.address') }}
              </div>
            @endif

            @if (!empty(Setting::value('company.phone')))
              <div>
                Tlp. {{ Setting::value('company.phone') }}
              </div>
            @endif
            @if (!empty(Setting::value('company.website')))
              <div>
                Website: {{ Setting::value('company.website') }}
              </div>
            @endif
          </div>
        </td>
      </tr>
      <tr>
        <td class="text-center">
          <hr style="margin: 4px 0;">
          @if (!empty($item->customer_name))
            <b>Pelanggan:</b><br>
            {{ $item->customer_code }}
            <br>{{ $item->customer_name }}
            @if (!empty($item->customer_address))
              <br>{{ $item->customer_address }}
            @endif
            @if (!empty($item->customer_phone))
              <br>{{ $item->customer_phone }}
            @endif
          @endif
        </td>
      </tr>
      <tr>
        <td class="text-center">
          <hr style="margin: 4px 0;">
          <b>#{{ $item->code }}</b><br>
          {{ format_datetime($item->datetime) }}
        </td>
      </tr>
      <tr>
        <td>
          <hr style="margin: 4px 0;">
          @php
            $total_cost = 0;
            $total_price = 0;
          @endphp
          @foreach ($item->details as $i => $detail)
            <div>
              {{ $i + 1 }})
              {{ format_number(abs($detail->quantity)) }} {{ $detail->product_uom }}
              {{ $detail->product->name }}
              @Rp.{{ format_number($detail->price) }}
              = Rp.{{ format_number(abs($detail->subtotal_price)) }}
            </div>
          @endforeach
          <hr style="margin: 4px 0;">
        </td>
      </tr>
      <tr>
        <td class="text-right">
          <b>Total: Rp. {{ format_number($item->grand_total + $item->total_discount) }}</b>
        </td>
      </tr>
      <tr>
        <td class="text-right">
          <b>Diskon Akhir: Rp. -{{ format_number($item->total_discount) }}</b>
        </td>
      </tr>
      <tr>
        <td class="text-right">
          <b>Grand Total: Rp. {{ format_number(abs($item->grand_total)) }}</b>
        </td>
      </tr>
      <tr>
        <td style="font-size: smaller;">
          <br style="margin: 4px 0;">
          <div class="text-center">
            <i>{{ Setting::value('pos.foot_note', 'Terima kasih sudah berbelanja.') }}</i>
          </div>
          <br style="margin: 4px 0;">
          <div class="text-center">
            @if ($item->cashierSession)
              <div>
                L: {{ $item->cashierSession->cashierTerminal?->location }} |
                T: {{ $item->cashierSession->cashierTerminal?->name }} | K:
                {{ $item->cashier->username }}
              </div>
            @endif
            Dicetak: {{ Auth::user()->username }} | {{ format_datetime(current_datetime()) }}
            <br>
            {{ config('app.name') . ' v' . config('app.version_str') }}
          </div>
        </td>
      </tr>
    </table>
  </div>
@endSection
