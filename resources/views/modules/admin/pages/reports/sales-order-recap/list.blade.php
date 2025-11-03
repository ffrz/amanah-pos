@php
    use App\Models\Setting;

    // Persiapan variabel
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

    // Inisialisasi Total Keseluruhan
    $total_sum = [
        'total_item' => 0,
        'total_price' => 0,
        'grand_total' => 0,
    ];

    foreach ($items as $item) {
        $total_sum['total_item'] += $item->total_item;
        $total_sum['total_price'] += $item->total_price;
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
                        {{ format_date($filter['start_date']) }} s/d
                        {{ format_date($filter['end_date']) }}
                    </td>
                </tr>
            </table>
        </x-admin.report.header>

        <table class="table table-bordered table-striped table-condensed center-th table-sm" style="width:100%">
            <thead>
                <tr>
                    @foreach ($headers as $col_key => $col_label)
                        <th>{{ $col_label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $i => $item)
                    <tr>
                        @foreach ($headers as $col_key => $col_label)
                            <td
                                class="{{ in_array($col_key, ['total_price', 'grand_total', 'total_paid', 'balance_due', 'total_item']) ? 'text-right' : 'text-left' }}">
                                @if (in_array($col_key, ['total_price', 'grand_total', 'total_paid', 'balance_due']))
                                    {{ format_number($item->$col_key) }}
                                @elseif ($col_key == 'total_item')
                                    {{ format_number($item->$col_key) }}
                                @elseif ($col_key == 'customer')
                                    @if ($item->customer_code)
                                        {{ $item->customer_code }} - {{ $item->customer_name }}
                                    @endif
                                @else
                                    {{ $item->$col_key }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td class="text-center" colspan="{{ count($headers) }}">
                            Data transaksi tidak tersedia dalam periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if (count($items) > 0)
                <tfoot>
                    <tr class="font-weight-bold" style="background-color: #f0f0f0;">
                        <th class="text-right" colspan="{{ count($headers) - 3 }}">TOTAL</th>
                        <th class="text-right">
                            {{ format_number($total_sum['total_item']) }}
                        </th>
                        <th class="text-right">
                            {{ format_number($total_sum['total_price']) }}
                        </th>
                        <th class="text-right">
                            {{ format_number($total_sum['grand_total']) }}
                        </th>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endSection
