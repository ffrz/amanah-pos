@php
    use App\Models\Setting;
    $is_pdf_export = isset($pdf) && $pdf;

    $logo_path = Setting::value('company.logo_path');
    $logo_path = $is_pdf_export ? public_path($logo_path) : url($logo_path);

    $title = '#' . $item->formatted_id;
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

        <table style="width:100%">
            <tr>
                @if ($logo_path)
                    <td>
                        <img src="{{ $logo_path }}" alt="" width="48" height="48">
                    </td>
                @endif
                <td>
                    <h5 class="m-0 text-primary">{{ Setting::value('company.name') }}</h5>
                    @if (!empty(Setting::value('company.headline')))
                        <h6 class="m-0">{{ Setting::value('company.headline') }}</h6>
                    @endif
                    <i>
                        @if (!empty(Setting::value('company.address')))
                            {{ Setting::value('company.address') }}<br>
                        @endif
                        @if (!empty(Setting::value('company.phone')))
                            Telp. {{ Setting::value('company.phone') }}
                        @endif
                        @if (!empty(Setting::value('company.website')))
                            - {{ Setting::value('company.website') }}
                        @endif
                    </i>
                    <br>
                </td>
                <td style="width:35%;;padding-left:10px;">
                    <table>
                        <tr>
                            <td style="width: 2cm;">No. Invoice</td>
                            <td>:</td>
                            <td>{{ $item->formatted_id }}</td>
                        </tr>
                        @if ($item->customer_id)
                            <tr>
                                <td style="vertical-align:top;">Pelanggan</td>
                                <td>:</td>
                                <td>
                                    {{ $item->customer_code }}<br />
                                    {{ $item->customer_name }}
                                </td>
                            </tr>
                            <tr>
                                <td>No. Telepon</td>
                                <td>:</td>
                                <td>{{ $item->customer_phone }}</td>
                            </tr>
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
                </td>
            </tr>
        </table>
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
                        <td class="text-right">{{ format_number(abs($detail->quantity)) }} {{ $detail->product->uom }}
                        </td>
                        <td class="text-right">{{ format_number($detail->price) }}</td>
                        <td class="text-right">{{ format_number(abs($subtotal_price)) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-right">Total</th>
                    <th class="text-right">{{ format_number(abs($item->total_price)) }}</th>
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
                    <th colspan="4" class="text-right">Grand Total</th>
                    <th class="text-right">{{ format_number(abs($item->grand_total)) }}</th>
                </tr>
            </tfoot>
        </table>
        <br>
        <table style="width:100%;">
            <tr>
                <td style="font-size: small;">
                    @if ($foot_note)
                        <div class="warning-notes">
                            Catatan:<br>
                            {!! $foot_note !!}
                        </div>
                    @endif
                    @if ($item->cashierSession)
                        <div>
                            Lokasi: {{ $item->cashierSession->cashierTerminal?->location }} |
                            Terminal: {{ $item->cashierSession->cashierTerminal?->name }} | Kasir:
                            {{ $item->cashier->name }}
                        </div>
                    @endif
                    <div>
                        Dicetak oleh <b>{{ Auth::user()->username }}</b> pada {{ format_datetime(now()) }} |
                        {{ env('APP_NAME') . ' v' . env('APP_VERSION_STR') }}
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
