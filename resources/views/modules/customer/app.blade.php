@extends('layouts.app')

@section('vite')
    @vite(['resources/js/modules/customer/app.js', 'resources/css/app.css'])
@endsection

@section('scripts')
  <script>
    window.CONSTANTS = <?= json_encode([
      'PRODUCT_TYPES' => \App\Models\Product::Types,
      'STOCKMOVEMENT_REFTYPES' => \App\Models\StockMovement::RefTypes,
      'STOCKADJUSTMENT_TYPES' => \App\Models\StockAdjustment::Types,
      'STOCKADJUSTMENT_STATUSES' => \App\Models\StockAdjustment::Statuses,
      'CUSTOMER_WALLET_TRANSACTION_TYPES' => \App\Models\CustomerWalletTransaction::Types,
      'FINANCE_ACCOUNT_TYPES' => \App\Models\FinanceAccount::Types,
      'FINANCE_TRANSACTION_TYPES' => \App\Models\FinanceTransaction::Types,
      'PURCHASE_ORDER_STATUSES' => \App\Models\PurchaseOrder::Statuses,
      'PURCHASE_ORDER_PAYMENT_STATUSES' => \App\Models\PurchaseOrder::PaymentStatuses,
      'PURCHASE_ORDER_DELIVERY_STATUSES' => \App\Models\PurchaseOrder::DeliveryStatuses,
    ]) ?>;
  </script>
@endsection