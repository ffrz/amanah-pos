@php
  use App\Models\Setting;

  $is_pdf_export = isset($pdf) && $pdf;

  $logo_path = Setting::value('company.logo_path');
  if ($logo_path) {
      $logo_path = $is_pdf_export ? public_path($logo_path) : url($logo_path);
  }

  $headers_count = 6;

  $total_sum = [
      'grand_total' => 0,
      'total_item' => 0,
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

    <table class="detail" style="width:100%">
      <tbody>
        @forelse ($items as $i => $item)
          @php $total_sum['grand_total'] += $item->grand_total; @endphp
          {{-- HEADER GROUPING TRANSAKSI (SALES ORDER) --}}
          <tr>
            <td style="padding: 5px; font-weight: bold;" colspan="{{ $headers_count }}">
              <div style="display: flex; justify-content: space-between; font-size: 1em;">
                <span>
                  {{ format_datetime($item->datetime) }} |
                  {{ $item->code }}
                </span>
                <span>
                  {{ $item->customer_code ? "{$item->customer_name} - {$item->customer_code}" : '- UMUM -' }}
                </span>
              </div>
            </td>
          </tr>

          {{-- RINCIAN DETAIL ITEM (NESTED TABLE) --}}
          <tr>
            <td style="padding: 0 0 0 0; border-top: none; background-color: #f7f7f7;" colspan="{{ $headers_count }}">

              <table class="table">
                <thead>
                  <tr>
                    <th style="font-size: 0.9em; padding: 3px 5px; text-align: right; width: 5%;">No.</th>
                    <th style="font-size: 0.9em; padding: 3px 5px; text-align: left; width: 40%;">Nama Item</th>
                    <th style="font-size: 0.9em; padding: 3px 5px; text-align: right;">Jml</th>
                    <th style="font-size: 0.9em; padding: 3px 5px; text-align: left;">Satuan</th>
                    <th style="font-size: 0.9em; padding: 3px 5px; text-align: right;">Harga Jual (Rp)</th>
                    <th style="font-size: 0.9em; padding: 3px 5px; text-align: right;">Sub Total (Rp)</th>
                  </tr>
                </thead>
                <tbody>
                  @php $total_item = 0 @endphp
                  @forelse ($item->details as $j => $detail)
                    @php $total_item += $detail->quantity @endphp
                    <tr>
                      <td style="font-size: 0.9em; padding: 3px 5px; text-align: right;">
                        {{ $j + 1 }}
                      </td>
                      <td style="font-size: 0.9em; padding: 3px 5px; text-align: left;">
                        {{ $detail->product_name }}
                      </td>
                      <td style="font-size: 0.9em; padding: 3px 5px; text-align: right;">
                        {{ format_number($detail->quantity) }}
                      </td>
                      <td style="font-size: 0.9em; padding: 3px 5px; text-align: left;">
                        {{ $detail->product_uom }}
                      </td>
                      <td style="font-size: 0.9em; padding: 3px 5px; text-align: right;">
                        {{ format_number($detail->price) }}
                      </td>
                      <td style="font-size: 0.9em; padding: 3px 5px; text-align: right;">
                        {{ format_number($detail->subtotal_price) }}
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td style="font-size: 0.9em; padding: 3px 5px; text-align: center;" colspan="{{ $headers_count }}">
                        Tidak ada rincian item.
                      </td>
                    </tr>
                  @endforelse
                  @php $total_sum['total_item'] += $total_item @endphp
                </tbody>

                @if (count($item->details) > 0)
                  <tfoot>
                    <tr>
                      <td style="font-size: 0.9em; padding: 3px 5px; text-align: right; font-weight: bold;" colspan="2">
                        TOTAL
                      </td>
                      <td class="text-right">{{ format_number($total_item) }}</td>
                      <td>items</td>
                      <td></td>
                      <td
                        style="font-size: 0.9em; padding: 3px 5px; text-align: right; font-weight: bold;">
                        {{ format_number($item->grand_total) }}
                      </td>
                    </tr>
                  </tfoot>
                @endif
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding: 5px 0;" colspan="100%"></td>
          </tr>
        @empty
          <tr>
            <td class="text-center" colspan="100%">
              Data transaksi tidak tersedia dalam periode ini.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>

    {{-- FOOTER GLOBAL --}}
    @if (count($items) > 0)
      <div class="text-right">
        <table class="summary" style="display:inline-block">
          <tr>
            <th>
              Grand Total Item
            </th>
            <th>:</th>
            <th></th>
            <th>
              {{ format_number($total_sum['total_item']) }}
            </th>
          </tr>
          <tr>
            <th>
              Grand Total Penjualan (Rp)
            </th>
            <th>:</th>
            <th>Rp.</th>
            <th>
              {{ format_number($total_sum['grand_total']) }}
            </th>
          </tr>
        </table>
      </div>
    @endif
  </div>
@endSection
