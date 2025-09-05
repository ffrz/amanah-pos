@extends('layouts.app')

@section('vite')
@vite(['resources/js/modules/admin/app.js', 'resources/css/app.css'])
@endsection

@section('scripts')
<script>
  window.CONSTANTS = <?= json_encode([
                        'USER_ROLES' => \App\Models\User::Roles,
                        'PRODUCT_TYPES' => \App\Models\Product::Types,
                        'CUSTOMER_TYPES' => \App\Models\Customer::Types,
                        'STOCKMOVEMENT_REFTYPES' => \App\Models\StockMovement::RefTypes,
                        'STOCKADJUSTMENT_TYPES' => \App\Models\StockAdjustment::Types,
                        'STOCKADJUSTMENT_STATUSES' => \App\Models\StockAdjustment::Statuses,
                        'CUSTOMER_TYPES' => \App\Models\Customer::Types,
                        'CUSTOMER_WALLET_TRANSACTION_TYPES' => \App\Models\CustomerWalletTransaction::Types,
                        'CUSTOMER_WALLET_TRANSACTION_CONFIRMATION_STATUSES' => \App\Models\CustomerWalletTransactionConfirmation::Statuses,
                        'FINANCE_ACCOUNT_TYPES' => \App\Models\FinanceAccount::Types,
                        'FINANCE_TRANSACTION_TYPES' => \App\Models\FinanceTransaction::Types,
                        'PURCHASE_ORDER_STATUSES' => \App\Models\PurchaseOrder::Statuses,
                        'PURCHASE_ORDER_PAYMENT_STATUSES' => \App\Models\PurchaseOrder::PaymentStatuses,
                        'PURCHASE_ORDER_DELIVERY_STATUSES' => \App\Models\PurchaseOrder::DeliveryStatuses,
                        'SALES_ORDER_STATUSES' => \App\Models\SalesOrder::Statuses,
                        'SALES_ORDER_PAYMENT_STATUSES' => \App\Models\SalesOrder::PaymentStatuses,
                        'SALES_ORDER_DELIVERY_STATUSES' => \App\Models\SalesOrder::DeliveryStatuses,
                      ]) ?>;
  window.CONSTANTS.USER_ROLE_ADMIN = "{{ \App\Models\User::Role_Admin }}";
  window.CONSTANTS.USER_ROLE_CASHIER = "{{ \App\Models\User::Role_Cashier }}";
  window.CONSTANTS.USER_ROLE_OWNER = "{{ \App\Models\User::Role_Owner }}";
</script>
@endsection