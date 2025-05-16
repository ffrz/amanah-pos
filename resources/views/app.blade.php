<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title inertia>{{ config('app.name', 'Laravel') }}</title>

  <!-- Fonts -->
  <link href="https://fonts.bunny.net" rel="preconnect">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

  <!-- Scripts -->
  <script>
    window.CONFIG = {}
    window.CONFIG.LOCALE = "{{ app()->getLocale() }}";
    window.CONFIG.APP_NAME = "{{ config('app.name', 'Laravel') }}";
    window.CONFIG.APP_VERSION = {{ config('app.version', 0x010000) }};
    window.CONFIG.APP_VERSION_STR = "{{ config('app.version_str', '1.0.0') }}";
    window.CONSTANTS = <?= json_encode([
          'USER_ROLES' => \App\Models\User::Roles,
          'PRODUCT_TYPES' => \App\Models\Product::Types,
          'STOCKMOVEMENT_REFTYPES' => \App\Models\StockMovement::RefTypes,
          'STOCKADJUSTMENT_TYPES' => \App\Models\StockAdjustment::Types,
          'STOCKADJUSTMENT_STATUSES' => \App\Models\StockAdjustment::Statuses,

          'SERVICEORDER_ORDERSTATUSES' => \App\Models\ServiceOrder::OrderStatuses,
          'SERVICEORDER_SERVICESTATUSES' => \App\Models\ServiceOrder::ServiceStatuses,
          'SERVICEORDER_PAYMENTSTATUSES' => \App\Models\ServiceOrder::PaymentStatuses,
          'SERVICEORDER_REPAIRSTATUSES' => \App\Models\ServiceOrder::RepairStatuses,
          'WASHORDER_ORDERSTATUSES' => \App\Models\WashOrder::OrderStatuses,
          'WASHORDER_SERVICESTATUSES' => \App\Models\WashOrder::ServiceStatuses,
          'WASHORDER_PAYMENTSTATUSES' => \App\Models\WashOrder::PaymentStatuses,
      ]) ?>;
    window.CONSTANTS.USER_ROLE_ADMIN = "{{ \App\Models\User::Role_Admin }}";
    window.CONSTANTS.USER_ROLE_CASHIER = "{{ \App\Models\User::Role_Cashier }}";
    window.CONSTANTS.USER_ROLE_WASHER = "{{ \App\Models\User::Role_Washer }}";
    window.CONSTANTS.USER_ROLE_OWNER = "{{ \App\Models\User::Role_Owner }}";
  </script>
  @routes
  @vite(['resources/js/app.js', 'resources/css/app.css'])

  @inertiaHead
</head>

<body class="font-sans antialiased">
  @inertia
</body>

</html>
