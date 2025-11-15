@php
    use App\Models\Setting;
@endphp

@props(['logoPath', 'title'])

<div>
    <table style="width:100%">
        <tr>
            @if ($logoPath)
                <td style="width:1.5cm; vertical-align: top; text-align:center;">
                    <img src="{{ $logoPath }}" alt="Logo Perusahaan" width="36" height="36">
                </td>
            @endif
            <td style="vertical-align: top;">
                <h3 style="margin: 0; text-align: Left;">{{ $title }}</h3>
                <h4 class="m-0" style="padding-top:5px">{{ Setting::value('company.name') }}</h4>
            </td>
            <td style="width:40%; padding-left:10px; vertical-align: top;">
                {{ $slot }}
            </td>
        </tr>
    </table>
    <div style="padding: 10px 0;font-size:smaller;color:#444;">
        Dibuat oleh <b>{{ Auth::user()->username }}</b> pada {{ format_datetime(now()) }}
    </div>
</div>
