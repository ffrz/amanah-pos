@php
    use App\Models\Setting;

    $is_pdf_export = isset($pdf) && $pdf;

    $logo_path = Setting::value('company.logo_path');
    $logo_path = $is_pdf_export ? public_path($logo_path) : url($logo_path);

    $title = 'KARTU STOK #' . $item->formatted_id;
    $foot_note = Setting::value('pos.foot_note');
@endphp

@extends('modules.admin.layouts.print-a4-portrait')

@section('content')
    @if (!$is_pdf_export)
        <div class="no-print text-center" style="margin-top: 10px; margin-bottom:20px;">
            <a class="btn my-xs" href="{{ route('admin.stock-adjustment.print-stock-card', $item->id) }}?output=pdf">Simpan
                PDF</a>
            <a class="btn my-xs" href="#" onclick="window.print()">Cetak</a>
        </div>
    @endif

    <div class="page">

        <x-admin.company-header :logo-path="$logo_path">
            <h4 style="margin: 0 0 5px 0; text-align: Left;">KARTU STOK</h4>
            <table>
                <tr>
                    <td style="width: 1.6cm;">No.</td>
                    <td>:</td>
                    <td>{{ $item->formatted_id }}</td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>:</td>
                    <td>{{ format_datetime($item->datetime) }}</td>
                </tr>
                <tr>
                    <td>Jenis</td>
                    <td>:</td>
                    <td>{{ \App\Models\StockAdjustment::Types[$item->type] }}</td>
                </tr>
                <tr>
                    <td style="vertical-align:top;">Catatan</td>
                    <td>:</td>
                    <td>{{ $item->notes ?? '-' }}</td>
                </tr>
            </table>
        </x-admin.company-header>
        <br>
        <table class="table table-bordered table-striped table-condensed center-th table-sm" style="width:100%">
            <thead>
                <th style="width:1%">No</th>
                <th>Nama Produk</th>
                <th style="width:10%">Satuan</th>
                <th style="width:15%">Stok Awal</th>
                <th style="width:15%">Stok Aktual</th>
                <th style="width:15%">Selisih</th>
            </thead>
            <tbody>
                @foreach ($details as $i => $detail)
                    <tr style="height:1cm;">
                        <td class="text-right">{{ $i + 1 }}</td>
                        <td>{{ $detail->product_name }}</td>
                        <td class="text-center">{{ $detail->uom }}</td>
                        <td class="text-right">{{ format_number($detail->old_quantity) }}
                        </td>
                        <td class="text-right"></td>
                        <td class="text-right"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <br>

        <table style="width:100%;">
            <tr>
                <td style="font-size: small; width: 60%;">
                    <div>
                        Dicetak oleh <b>{{ Auth::user()->username }}</b> pada {{ format_datetime(now()) }} |
                        {{ env('APP_NAME') . ' v' . env('APP_VERSION_STR') }}
                    </div>
                    @if ($item->creator)
                        <div>
                            Dibuat oleh: {{ $item->creator->name ?? '-' }}
                        </div>
                    @endif
                </td>
                <td style="width:40%;text-align:center;">
                    Dikonfirmasi Oleh,<br><br><br><br>
                    (................................)
                </td>
            </tr>
        </table>
    </div>
@endSection
