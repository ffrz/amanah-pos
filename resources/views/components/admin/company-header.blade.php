{{-- resources/views/components/print/company-header.blade.php --}}

@php
  use App\Models\Setting;
@endphp

@props(['logoPath'])

<table style="width:100%">
  <tr>
    {{-- BAGIAN KIRI: Company Info (Statis) --}}
    @if ($logoPath)
      <td style="width:1.5cm; vertical-align: top; text-align:center;">
        <img src="{{ $logoPath }}" alt="Logo Perusahaan" width="48" height="48">
      </td>
    @endif

    <td style="vertical-align: top;">
      {{-- Data Perusahaan diambil langsung dari Setting --}}
      <h5 class="m-0 text-primary">{{ Setting::value('company.name') }}</h5>
      @if (!empty(Setting::value('company.headline')))
        <h6 class="m-0">{{ Setting::value('company.headline') }}</h6>
      @endif
      <i style="font-size: small;">
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
    </td>

    {{-- BAGIAN KANAN: Slot Dinamis --}}
    <td style="width:40%; padding-left:10px; vertical-align: top;">
      {{ $slot }}
    </td>
  </tr>
</table>
