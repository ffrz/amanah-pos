@php
    use App\Models\Setting;

    // Persiapan variabel
    $is_pdf_export = isset($pdf) && $pdf;

    $logo_path = Setting::value('company.logo_path');
    if ($logo_path) {
        $logo_path = $is_pdf_export ? public_path($logo_path) : url($logo_path);
    }

    // Karena format laporan diubah menjadi detail item yang digrouping,
    // $headers yang berasal dari controller tidak lagi relevan untuk header tabel utama.
    // Kita akan menggunakan informasi transaksi sebagai GROUPING HEADER di <tbody>.
    $headers_count = 6; // Jumlah kolom rincian item: No., Nama, Jml, Satuan, Harga, Subtotal

    $layout_extension = 'modules.admin.layouts.print-a4-' . $orientation;

    // --- Definisi Header untuk Tabel Rincian Item (Detail) ---
    // Kolom baru: No., Nama, Jml, Satuan, Harga, Subtotal
    $detail_headers = [
        'no' => 'No.',
        'product_name' => 'Nama Item',
        'quantity' => 'Jml',
        'product_uom' => 'Satuan',
        'price' => 'Harga Jual (Rp)',
        'subtotal_price' => 'Sub Total (Rp)',
    ];

    // Inisialisasi Total Keseluruhan (Grand Total Summary)
    $total_sum = [
        'grand_total' => 0,
        'total_item' => 0,
    ];

    // Hitung total hanya untuk Grand Total (karena ini adalah laporan grouping)
    foreach ($items as $item) {
        $total_sum['grand_total'] += $item->grand_total;
    }
@endphp

@extends($layout_extension)

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
                {{-- Tambahkan filter lain seperti Pelanggan di sini jika diperlukan --}}
            </table>
        </x-admin.report.header>

        {{-- Keterangan: table-condensed dan table-sm digunakan untuk menghemat ruang pada cetakan A4 --}}
        <table class="detail" style="width:100%">
            {{-- Hapus <thead>, fokus pada body dengan grouping --}}
            <tbody>
                @forelse ($items as $i => $item)
                    {{-- BARIS 1: HEADER GROUPING TRANSAKSI (SALES ORDER) --}}
                    <tr>
                        <td colspan="{{ count($detail_headers) }}" style="padding: 5px; font-weight: bold;">
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

                    {{-- BARIS 2: RINCIAN DETAIL ITEM (NESTED TABLE) --}}
                    <tr>
                        {{-- Colspan mencakup seluruh lebar tabel header --}}
                        <td colspan="{{ count($detail_headers) }}"
                            style="padding: 0 0 0 0; border-top: none; background-color: #f7f7f7;">

                            {{-- Nested Table untuk Rincian Item --}}
                            <table class="table">
                                <thead>
                                    <tr>
                                        @foreach ($detail_headers as $det_key => $det_label)
                                            <th
                                                style="font-size: 0.9em; padding: 3px 5px; 
                                                    {{ in_array($det_key, ['no', 'quantity', 'price', 'subtotal_price']) ? 'text-align: right;' : 'text-align: left;' }}
                                                    {{ $det_key == 'no' ? 'width: 5%;' : '' }}
                                                    {{ $det_key == 'product_name' ? 'width: 40%;' : '' }}
                                                    ">
                                                {{ $det_label }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Loop melalui SalesOrderDetail --}}
                                    @php $total_item = 0 @endphp
                                    @forelse ($item->details as $j => $detail)
                                        @php $total_item += $detail->quantity @endphp
                                        <tr>
                                            @foreach ($detail_headers as $det_key => $det_label)
                                                <td
                                                    style="font-size: 0.9em; padding: 3px 5px; 
                                                        {{ in_array($det_key, ['no', 'quantity', 'price', 'subtotal_price']) ? 'text-align: right;' : 'text-align: left;' }}">

                                                    @if ($det_key == 'no')
                                                        {{ $j + 1 }}
                                                    @elseif (in_array($det_key, ['price', 'subtotal_price']))
                                                        {{ format_number($detail->$det_key) }}
                                                    @elseif ($det_key == 'quantity')
                                                        {{ format_number($detail->$det_key) }}
                                                    @else
                                                        {{ $detail->$det_key }}
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ count($detail_headers) }}"
                                                style="font-size: 0.9em; padding: 3px 5px; text-align: center;">
                                                Tidak ada rincian item.
                                            </td>
                                        </tr>
                                    @endforelse
                                    @php $total_sum['total_item'] += $total_item @endphp
                                </tbody>
                                {{-- Sub-total per transaksi --}}
                                @if (count($item->details) > 0)
                                    <tfoot>
                                        <tr>
                                            <td colspan="2"
                                                style="font-size: 0.9em; padding: 3px 5px; text-align: right; font-weight: bold;">
                                                TOTAL
                                            </td>
                                            <td class="text-right">{{ format_number($total_item) }}</td>
                                            <td>items</td>
                                            <td></td>
                                            <td
                                                style="font-size: 0.9em; padding: 3px 5px; text-align: right; font-weight: bold;">
                                                {{-- Karena total_price adalah sub-total sebelum diskon, lebih baik tampilkan grand_total transaksi di footer ini --}}
                                                {{ format_number($item->grand_total) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </td>
                    </tr>

                    {{-- Tambahkan jarak antar transaksi --}}
                    <tr>
                        <td colspan="100%" style="padding: 5px 0;"></td>
                    </tr>
                @empty
                    <tr>
                        <td class="text-center" colspan="{{ count($detail_headers) }}">
                            Data transaksi tidak tersedia dalam periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- FOOTER GLOBAL --}}
        @if (count($items) > 0)
            <div class="text-right">
                <table style="display:inline-block" class="summary">
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
