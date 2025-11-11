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
        'total_cost' => 0,
        'total_profit' => 0,
    ];

@endphp

@extends('modules.admin.layouts.print-a4-portrait')

@section('content')
    <div class="page">

        <x-admin.report.header :logo-path="$logo_path" title="LAPORAN REKAPITULASI LABA">
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
                <tr class="bg-gray-200">
                    <th class="text-left">No. Transaksi</th>
                    <th class="text-left">Waktu</th>
                    <th class="text-left">Pelanggan</th>
                    <th class="text-right">Grand Total (A)</th>
                    <th class="text-right">Total HPP (B)</th>
                    <th class="text-right">Laba Kotor (A - B)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $i => $item)
                    @php
                        $totals['total_item'] += $item->total_item;
                        $totals['grand_total'] += $item->grand_total;
                        $totals['total_cost'] += $item->total_cost;
                        $totals['total_profit'] += $item->total_profit;
                    @endphp
                    <tr>
                        <td class="text-left">{{ $item->code }}</td>
                        <td class="text-left">{{ format_datetime($item->datetime) }}</td>
                        <td class="text-left">
                            @if ($item->customer_code)
                                {{ $item->customer_code }} - {{ $item->customer_name }}
                            @endif

                        </td>
                        <td class="text-right">{{ format_number($item->grand_total) }}</td>
                        <td class="text-right">{{ format_number($item->total_cost) }}</td>
                        <td class="text-right font-weight-bold">{{ format_number($item->total_profit) }}
                        @empty
                            Data transaksi laba tidak tersedia dalam periode ini.
                @endforelse

                @if (count($items) > 0)
            <tfoot>
                <tr class="font-weight-bold" style="background-color: #f0f0f0;">
                    <th class="text-right" colspan="3">TOTAL</th>
                    <th class="text-right">
                        {{ format_number($totals['grand_total']) }}
                    </th>
                    <th class="text-right">
                        {{ format_number($totals['total_cost']) }}
                    </th>
                    <th class="text-right">
                        {{ format_number($totals['total_profit']) }}
                    </th>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
@endSection
