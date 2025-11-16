@php
    use App\Models\Setting;
    use App\Models\FinanceTransaction;
    use App\Models\FinanceAccount;

    $is_pdf_export = isset($pdf) && $pdf;

    $logo_path = Setting::value('company.logo_path');
    if ($logo_path) {
        $logo_path = $is_pdf_export ? public_path($logo_path) : url($logo_path);
    }

    $types = FinanceTransaction::Types;
    $total_credit = 0;
    $total_debit = 0;
@endphp

@extends('modules.admin.layouts.print-a4-portrait')

@section('content')
    <div class="page">
        {{-- HEADER LAPORAN --}}
        <x-admin.report.header :logo-path="$logo_path" :title="$title">
            <table class="report-header-info">
                {{-- Periode Tanggal --}}
                <tr>
                    <td style="width: 3cm;">Periode</td>
                    <td>:</td>
                    <td>
                        {{ format_date($filter['start_date']) }} s/d {{ format_date($filter['end_date']) }}
                    </td>
                </tr>

                {{-- Jenis Transaksi --}}
                @if (isset($filter['type']) && $filter['type'] !== 'all')
                    <tr>
                        <td>Jenis</td>
                        <td>:</td>
                        <td>
                            {{ $types[$filter['type']] ?? $filter['type'] }}
                        </td>
                    </tr>
                @endif

                {{-- Informasi Filter Akun (Jika dipilih) --}}
                @if (isset($filter['accounts']) && !empty(array_filter((array) $filter['accounts'])))
                    <tr>
                        <td>Akun</td>
                        <td>:</td>
                        <td>
                            {{ count((array) $filter['accounts']) > 1 ? 'Beberapa Akun Dipilih' : '1 Akun Dipilih' }}
                        </td>
                    </tr>
                @endif

                {{-- [BARU] Informasi Filter Kategori (Jika dipilih) --}}
                @if (isset($filter['categories']) && !empty(array_filter((array) $filter['categories'])))
                    @php
                        $category_list = array_filter((array) $filter['categories']);
                        $has_none = in_array('none', $category_list);
                        $id_count = count(array_filter($category_list, 'is_numeric'));

                        $category_text = '';
                        if ($has_none && $id_count > 0) {
                            $category_text = 'Tanpa Kategori & Kategori Lainnya';
                        } elseif ($has_none) {
                            $category_text = 'Tanpa Kategori';
                        } elseif ($id_count > 1) {
                            $category_text = 'Beberapa Kategori Dipilih';
                        } elseif ($id_count == 1) {
                            $category_text = '1 Kategori Dipilih';
                        }
                    @endphp
                    @if (!empty($category_text))
                        <tr>
                            <td>Kategori</td>
                            <td>:</td>
                            <td>{{ $category_text }}</td>
                        </tr>
                    @endif
                @endif

                {{-- [BARU] Informasi Filter Tags (Jika dipilih) --}}
                @if (isset($filter['tags']) && !empty(array_filter((array) $filter['tags'])))
                    <tr>
                        <td>Label</td>
                        <td>:</td>
                        <td>
                            {{ count((array) $filter['tags']) > 1 ? 'Beberapa Label Dipilih' : '1 Label Dipilih' }}
                        </td>
                    </tr>
                @endif

            </table>
        </x-admin.report.header>

        {{-- DETAIL TRANSAKSI --}}
        <table class="table table-bordered table-striped table-condensed center-th table-sm" style="width:100%">
            <thead>
                <tr>
                    <th style="width: 1%">No</th>
                    <th style="width: 10%">Tanggal</th>
                    <th style="width: 15%">Akun</th>
                    <th style="width: 15%">Kategori</th>
                    <th>Keterangan</th>
                    <th style="width: 12%">Pemasukan(Rp.)</th>
                    <th style="width: 12%">Pengeluaran(Rp.)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $i => $item)
                    @php
                        // [PERBAIKAN] Gunakan abs() untuk pengeluaran agar nilainya positif
                        $debit = $item->type == FinanceTransaction::Type_Expense ? abs($item->amount) : 0;
                        $credit = $item->type == FinanceTransaction::Type_Income ? $item->amount : 0;

                        $total_credit += $credit;
                        $total_debit += $debit;
                    @endphp
                    <tr>
                        <td class="text-right">{{ $i + 1 }}</td>
                        <td class="text-center">{{ format_date($item->datetime, 'd-m-Y') }}</td>
                        <td class="text-left">{{ $item->account->name ?? '-' }}</td>
                        <td class="text-left">{{ $item->category->name ?? 'Tanpa Kategori' }}</td>
                        <td class="text-left">
                            {{ $item->notes }}
                            @if ($item->tags->isNotEmpty())
                                <div style="font-size: 0.8em; color: #666;">
                                    {{ $item->tags->pluck('name')->map(fn($tag) => '#' . $tag)->implode(', ') }}
                                </div>
                            @endif
                        </td>

                        {{-- Pemasukan (Credit) --}}
                        <td class="text-right">
                            {{ $credit > 0 ? format_number($credit) : '-' }}
                        </td>

                        {{-- Pengeluaran (Debit) --}}
                        <td class="text-right">
                            {{ $debit > 0 ? format_number($debit) : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        {{-- [PERBAIKAN] Colspan harus 7, sesuai jumlah <th> --}}
                        <td colspan="7" class="text-center">Data transaksi tidak tersedia dalam periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
            {{-- BARIS TOTAL --}}
            @if (count($items) > 0)
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-right" style="font-weight: bold;">
                            TOTAL PERIODE INI
                        </th>
                        <th class="text-right" style="font-weight: bold;">
                            {{ format_number($total_credit) }}
                        </th>
                        <th class="text-right" style="font-weight: bold;">
                            {{ format_number($total_debit) }}
                        </th>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endSection
