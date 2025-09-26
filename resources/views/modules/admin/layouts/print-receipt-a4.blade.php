@php use App\Models\Setting; @endphp
<!DOCTYPE html>
<html class="page-a4" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    @if (isset($pdf) && $pdf === true)
        <style type="text/css">
            <?php echo file_get_contents(public_path('assets/css/print.css')); ?>
        </style>
    @else
        <link href="/assets/css/print.css" rel="stylesheet">
        <style>
            @media screen {
                body {
                    background: #fafafa;
                }

                .page {
                    background: #fff;
                    width: 21cm;
                    padding: 1cm;
                    border: 1px solid #888;
                    box-shadow: #bbb 5px 5px 10px;
                    margin: 0 auto;
                }
            }
        </style>
    @endif
    @vite([])
</head>

<body>
    @yield('content')
    <script>
        window.addEventListener("load", window.print());
    </script>
</body>

</html>
