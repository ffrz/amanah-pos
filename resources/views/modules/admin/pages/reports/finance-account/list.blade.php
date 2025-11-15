@php
    use App\Models\Setting;
    use App\Models\FinanceAccount;

    $is_pdf_export = isset($pdf) && $pdf;

    $logo_path = Setting::value('company.logo_path');
    if ($logo_path) {
        $logo_path = $is_pdf_export ? public_path($logo_path) : url($logo_path);
    }

    $total_balance = 0;
    foreach ($items as $item) {
        $total_balance += $item->balance;
    }
@endphp

@extends('modules.admin.layouts.print-a4-portrait')

@section('content')
    <div class="page">
        <x-admin.report.header :logo-path="$logo_path" :title="$title">
            <table class="report-header-info">
                <tr>
                    <td style="width: 2cm;">Status</td>
                    <td>:</td>
                    <td>
                        {{ $filter['status'] ? ($filter['status'] == 'all' ? 'Semua' : ($filter['status'] == 'active' ? 'Aktif' : 'Tidak Aktif')) : 'Semua' }}
                    </td>
                </tr>

                @if ($filter['type'] != 'all')
                    <tr>
                        <td style="width: 2cm;">Jenis</td>
                        <td>:</td>
                        <td>
                            {{ \App\Models\FinanceAccount::Types[$filter['type']] }}
                        </td>
                    </tr>
                @endif

            </table>
        </x-admin.report.header>

        <table class="table table-bordered table-striped table-condensed center-th table-sm" style="width:100%">
            <thead>
                <tr>
                    <th style="width:1%">No</th>
                    <th>Nama</th>
                    <th style="width: 20%">Jenis Akun</th>
                    <th style="width: 15%">Saldo (Rp.)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $i => $item)
                    <tr>
                        <td class="text-right">{{ $i + 1 }}</td>

                        {{-- Kolom 1: Nama --}}
                        <td class="text-left">
                            {{ $item->name }}
                        </td>

                        {{-- Kolom 2: Jenis Akun --}}
                        <td class="text-left">
                            {{ \App\Models\FinanceAccount::Types[$item->type] ?? $item->type }}
                        </td>

                        {{-- Kolom 3: Saldo --}}
                        <td class="text-right">
                            {{ format_number($item->balance) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Data tidak tersedia.</td>
                    </tr>
                @endforelse
            </tbody>
            {{-- BARIS TOTAL SALDO --}}
            @if (count($items) > 0)
                <tfoot>

                    <tr>
                        <th colspan="3" class="text-right" style="font-weight: bold;">
                            TOTAL SALDO AKUN
                        </th>
                        <th class="text-right" style="font-weight: bold;">
                            {{ format_number($total_balance) }}
                        </th>
                    </tr>

                </tfoot>
            @endif
        </table>
    </div>
@endSection
