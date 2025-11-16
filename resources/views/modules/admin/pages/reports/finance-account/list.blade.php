@php
    use App\Models\Setting;
    use App\Models\FinanceTransaction;

    $is_pdf_export = isset($pdf) && $pdf;

    $logo_path = Setting::value('company.logo_path');
    if ($logo_path) {
        $logo_path = $is_pdf_export ? public_path($logo_path) : url($logo_path);
    }

    // Hitung Total Income dan Expense
    $total_income = 0;
    $total_expense = 0;

    foreach ($items as $item) {
        // Transaksi Transfer biasanya diabaikan dari total income/expense report,
        // namun kita akan memasukkannya jika amount positif/negatif
        if ($item->amount > 0) {
            $total_income += $item->amount;
        } else {
            $total_expense += abs($item->amount);
        }
    }

    $net_flow = $total_income - $total_expense;

    $layout_extension = 'modules.admin.layouts.print-a4-' . $orientation;
@endphp

@extends($layout_extension)

@section('content')
    <div class="page">
        <x-admin.report.header :logo-path="$logo_path" :title="$title">
            <table class="report-header-info">
                {{-- Tampilkan Filter yang Diterapkan --}}
                @if (!empty($filter['start_date']) || !empty($filter['end_date']))
                    <tr>
                        <td>Rentang Waktu</td>
                        <td>:</td>
                        <td>{{ $filter['start_date'] ?? '-' }} s/d {{ $filter['end_date'] ?? '-' }}</td>
                    </tr>
                @endif

                {{-- Anda bisa menambahkan filter lain di sini seperti Akun dan Kategori --}}
            </table>
        </x-admin.report.header>

        <table class="table table-bordered table-striped table-condensed center-th table-sm"
            style="width:100%; font-size: 10px;">
            <thead>
                <tr>
                    <th style="width:1%">No</th>
                    <th style="width:12%">Tgl & Waktu</th>
                    <th style="width:10%">Kode</th>
                    <th style="width:15%">Akun Keuangan</th>
                    <th style="width:10%">Jenis</th>
                    <th style="width:15%">Kategori</th>
                    <th style="width:15%">Jumlah (Rp.)</th>
                    <th>Catatan & Referensi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $i => $item)
                    <tr>
                        <td class="text-right">{{ $i + 1 }}</td>
                        <td>{{ $item->datetime->format('Y-m-d H:i') }}</td>
                        <td>{{ $item->code }}</td>
                        <td>{{ $item->account->name ?? '-' }}</td>
                        <td class="text-center">{{ $item->type_label }}</td>
                        <td>{{ $item->category->name ?? '-' }}</td>
                        <td class="text-right {{ $item->amount < 0 ? 'text-danger' : 'text-success' }}">
                            {{ format_number($item->amount) }}
                        </td>
                        <td>
                            {{ $item->notes }}
                            @if ($item->ref_type_label != '-')
                                <br><small>Ref: {{ $item->ref_type_label }}</small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data transaksi yang ditemukan dalam filter ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-right" style="font-weight: bold;">TOTAL PEMASUKAN</td>
                    <td class="text-right" style="font-weight: bold; color: green;">
                        {{ format_number($total_income) }}
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="6" class="text-right" style="font-weight: bold;">TOTAL PENGELUARAN</td>
                    <td class="text-right" style="font-weight: bold; color: red;">
                        ({{ format_number($total_expense) }})
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="6" class="text-right" style="font-weight: bold;">ARUS KAS BERSIH</td>
                    <td class="text-right" style="font-weight: bold; border-top: 2px solid #000;">
                        {{ format_number($net_flow) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
@endSection
