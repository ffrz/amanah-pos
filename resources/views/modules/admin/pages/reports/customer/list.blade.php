@php
    use App\Models\Setting;

    $price_types = [
        'all' => 'Semua',
        'price_1' => 'Harga Eceran',
        'price_2' => 'Harga Partai',
        'price_3' => 'Harga Grosir',
    ];

    $is_pdf_export = isset($pdf) && $pdf;

    $logo_path = Setting::value('company.logo_path');
    if ($logo_path) {
        $logo_path = $is_pdf_export ? public_path($logo_path) : url($logo_path);
    }

    $column_map = [
        'code' => 'Kode',
        'name' => 'Nama',
        'phone' => 'No Telepon',
        'address' => 'Alamat',
        'balance' => 'Utang / Piutang (Rp)',
        'wallet_balance' => 'Saldo Deposit (Rp)',
        'active' => 'Aktif / Nonaktif',
        'type' => 'Jenis Akun',
        'default_price_type' => 'Level Harga',
    ];

    $requested_columns = request('columns', array_keys($column_map));

    $headers = [];
    foreach ($requested_columns as $col_key) {
        if (isset($column_map[$col_key])) {
            $headers[$col_key] = $column_map[$col_key];
        }
    }

    $layout_extension = 'modules.admin.layouts.print-a4-' . $orientation;
@endphp

@extends($layout_extension)

@section('content')
    <div class="page" style="margin-top:5mm">
        {{-- HEADER LAPORAN --}}
        <x-admin.company-header :logo-path="$logo_path">
            <h4 style="margin: 0 0 5px 0; text-align: Left;">{{ $title }}</h4>
            {{-- Bagian Filter Laporan --}}
            <table>
                {{-- Contoh Filter Status (Diambil dari request) --}}
                <tr>
                    <td style="width: 2cm;">Status</td>
                    <td>:</td>
                    <td>
                        {{ request('filter.status') ? (request('filter.status') == 'all' ? 'Semua' : (request('filter.status') == 'active' ? 'Aktif' : 'Tidak Aktif')) : 'Semua' }}
                    </td>
                </tr>

                <tr>
                    <td style="width: 2cm;">Jenis</td>
                    <td>:</td>
                    <td>
                        {{ request('filter.type') == 'all' ? 'Semua' : \App\Models\Customer::Types[request('filter.type')] }}
                    </td>
                </tr>

                <tr>
                    <td style="width: 2cm;">Level Harga</td>
                    <td>:</td>
                    <td>
                        {{ $price_types[request('filter.default_price_type')] }}
                    </td>
                </tr>
            </table>
        </x-admin.company-header>
        <br>
        <div class="text-center">
            Dibuat oleh <b>{{ Auth::user()->username }}</b> pada {{ format_datetime(now()) }} |
            {{ env('APP_NAME') . ' v' . env('APP_VERSION_STR') }}
        </div>
        <br>
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
                                    {{ $col_key == 'balance' || $col_key == 'wallet_balance'
                                        ? 'text-right'
                                        : ($col_key == 'active'
                                            ? 'text-center'
                                            : 'text-left') }}">
                                @if ($col_key == 'balance' || $col_key == 'wallet_balance')
                                    {{ format_number($item->$col_key) }}
                                @elseif ($col_key == 'active')
                                    {{ $item->$col_key ? 'Aktif' : 'Tidak Aktif' }}
                                @elseif ($col_key == 'type')
                                    {{ \App\Models\Customer::Types[$item->$col_key] ?? '' }}
                                @elseif ($col_key == 'default_price_type')
                                    {{ $price_types[$item->$col_key] ?? '' }}
                                @else
                                    {{ $item->$col_key }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($headers) + 1 }}" class="text-center">Tidak ada data pelanggan.</td>
                    </tr>
                @endforelse
            </tbody>
            {{-- Opsional: Tambahkan Total/Summary di Tfoot jika diperlukan --}}
            {{-- <tfoot>
                <tr>
                    <th class="text-right" colspan="{{ count($headers) }}">Total Item</th>
                    <th class="text-right">{{ $items->count() }}</th>
                </tr>
            </tfoot> --}}
        </table>
    </div>
@endSection
