@php use App\Models\Setting; @endphp
<!DOCTYPE html>
<html class="page-a4" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link href="/assets/css/print.css" rel="stylesheet">
    @vite([])
</head>

<body>
    @yield('content')
    <script>
        // window.addEventListener("load", window.print());
    </script>
</body>

</html>
