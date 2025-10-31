@php
    use App\Models\Setting;
@endphp

@props(['logoPath'])

<table style="width:100%">
    <tr>
        @if ($logoPath)
            <td style="width:1.5cm; vertical-align: top; text-align:center;">
                <img src="{{ $logoPath }}" alt="Logo Perusahaan" width="36" height="36">
            </td>
        @endif

        <td style="vertical-align: top;">
            <h5 class="m-0" style="padding-top:10px">{{ Setting::value('company.name') }}</h5>
        </td>

        <td style="width:40%; padding-left:10px; vertical-align: top;">
            {{ $slot }}
        </td>
    </tr>
</table>
