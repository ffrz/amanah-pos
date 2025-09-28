<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  @php
    $isAdminRoute = request()->is('admin*');
    $manifestFile = $isAdminRoute ? 'admin-manifest.webmanifest' : 'customer-manifest.webmanifest';
  @endphp

  <title inertia>{{ config('app.name', 'Laravel') }}</title>

  {{-- MANIFEST LINK --}}
  <link href="{{ asset('build/' . $manifestFile) }}" rel="manifest">

  <!-- Fonts -->
  <link href="https://fonts.bunny.net" rel="preconnect">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

  <style>
    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }

    #loading-spinner {
      animation: spin 1.2s linear infinite;
    }
  </style>

  <!-- Scripts -->
  <script>
    window.CONFIG = {}
    window.CONFIG.LOCALE = "{{ app()->getLocale() }}";
    window.CONFIG.APP_NAME = "{{ config('app.name', 'Laravel') }}";
    window.CONFIG.APP_DEMO = "{{ env('APP_DEMO', false) }}";
    window.CONFIG.APP_VERSION = "{{ config('app.version', 0x010000) }}";
    window.CONFIG.APP_VERSION_STR = "{{ config('app.version_str', '1.0.0') }}";
  </script>
  @yield('scripts')
  @routes

  @yield('vite')
  @inertiaHead
</head>

<body class="font-sans antialiased">
  <div id="loading-screen"
    style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background-color: #fff; z-index: 9999;">
    <svg id="loading-spinner" width="30" height="30" viewBox="0 0 50 50">
      <circle cx="25" cy="25" r="20" fill="none" stroke="#007bff" stroke-width="4" stroke-dasharray="80" stroke-dashoffset="60" stroke-linecap="round"></circle>
    </svg>
  </div>

  @inertia
  <script>
    window.addEventListener('load', function() {
      var loadingScreen = document.getElementById('loading-screen');
      if (loadingScreen) {
        loadingScreen.style.display = 'none';
      }
    });
  </script>

  {{-- KODE PWA DI BAWAH SUDAH DIHAPUS karena Vite PWA sudah menangani pendaftaran --}}
  {{-- Blok Pendaftaran Service Worker yang salah telah dihapus --}}

</body>

</html>
