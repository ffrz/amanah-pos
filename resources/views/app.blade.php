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
    window.CONFIG.APP_VERSION = {
      {
        config('app.version', 0x010000)
      }
    };
    window.CONFIG.APP_VERSION_STR = "{{ config('app.version_str', '1.0.0') }}";
    window.CONSTANTS = <?= json_encode([
          'USER_TYPES' => \App\Models\User::Types,
          'PRODUCT_TYPES' => \App\Models\Product::Types,
          'STOCK_MOVEMENT_REF_TYPES' => \App\Models\StockMovement::RefTypes,
          'STOCK_ADJUSTMENT_TYPES' => \App\Models\StockAdjustment::Types,
          'STOCK_ADJUSTMENT_STATUSES' => \App\Models\StockAdjustment::Statuses,
          'CUSTOMER_WALLET_TRANSACTION_TYPES' => \App\Models\CustomerWalletTransaction::Types,
          'FINANCE_ACCOUNT_TYPES' => \App\Models\FinanceAccount::Types,
          'FINANCE_TRANSACTION_TYPES' => \App\Models\FinanceTransaction::Types,
          'PURCHASE_ORDER_STATUSES' => \App\Models\PurchaseOrder::Statuses,
          'PURCHASE_ORDER_PAYMENT_STATUSES' => \App\Models\PurchaseOrder::PaymentStatuses,
          'PURCHASE_ORDER_DELIVERY_STATUSES' => \App\Models\PurchaseOrder::DeliveryStatuses,
          'CUSTOMER_TYPES' => \App\Models\Customer::Types,
      ]) ?>;
    window.CONSTANTS.USER_TYPE_SUPER_USER = "{{ \App\Models\User::Type_SuperUser }}";
    window.CONSTANTS.USER_TYPE_STANDARD_USER = "{{ \App\Models\User::Type_StandardUser }}";
  </script>
  @routes
  @vite(['resources/js/app.js', 'resources/css/app.css'])

  @inertiaHead
</head>

<body class="font-sans antialiased">
  @inertia
</body>

</html>
