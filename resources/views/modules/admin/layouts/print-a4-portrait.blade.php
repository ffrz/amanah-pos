@php use App\Models\Setting; @endphp
<!DOCTYPE html>
<html class="page-a4 page-a4-portrait" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    @if (isset($pdf) && $pdf === true)
        <style type="text/css">
            @php echo file_get_contents(public_path('assets/css/print.css'))
            @endphp
            html {
                margin: 0.7cm;
            }
        </style>
    @else
        <link href="/assets/css/print.css" rel="stylesheet">
        <style>
            @media screen {
                body {
                    background: #fafafa;
                    max-width: 21cm;
                }

                .page-container {
                    width: 21cm;
                }

                .page {
                    padding: 1cm;
                    background: #fff;
                    border: 1px solid #888;
                    box-shadow: #bbb 5px 5px 10px;
                }
            }
        </style>
    @endif
    @vite([])
</head>

<body>
    <div class="page-container">
        @yield('content')
    </div>
    <script>
        // window.addEventListener("load", window.print());
    </script>
</body>

</html>
