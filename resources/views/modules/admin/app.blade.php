@extends('layouts.app')

@section('vite')
    @vite(['resources/js/modules/admin/app.js', 'resources/css/app.css'])
@endsection

@section('scripts')
    <script>
        window.CONSTANTS = <?= json_encode([
                'USER_TYPES' => \App\Models\User::Types,
                'PRODUCT_TYPES' => \App\Models\Product::Types,
                'PRODUCT_PRICE_TYPES' => \App\Models\Product::PriceTypes,
                'CUSTOMER_TYPES' => \App\Models\Customer::Types,
                'STOCK_MOVEMENT_REF_TYPES' => \App\Models\StockMovement::RefTypes,
                'STOCK_ADJUSTMENT_TYPES' => \App\Models\StockAdjustment::Types,
                'STOCK_ADJUSTMENT_STATUSES' => \App\Models\StockAdjustment::Statuses,
                'CUSTOMER_TYPES' => \App\Models\Customer::Types,
                'CUSTOMER_WALLET_TRANSACTION_TYPES' => \App\Models\CustomerWalletTransaction::Types,
                'CUSTOMER_WALLET_TRANSACTION_CONFIRMATION_STATUSES' => \App\Models\CustomerWalletTransactionConfirmation::Statuses,
                'FINANCE_ACCOUNT_TYPES' => \App\Models\FinanceAccount::Types,
                'FINANCE_TRANSACTION_TYPES' => \App\Models\FinanceTransaction::Types,
                'PURCHASE_ORDER_STATUSES' => \App\Models\PurchaseOrder::Statuses,
                'PURCHASE_ORDER_PAYMENT_STATUSES' => \App\Models\PurchaseOrder::PaymentStatuses,
                'PURCHASE_ORDER_DELIVERY_STATUSES' => \App\Models\PurchaseOrder::DeliveryStatuses,
                'PURCHASE_ORDER_PAYMENT_TYPES' => \App\Models\PurchaseOrderPayment::Types,
                'PURCHASE_ORDER_RETURN_STATUSES' => \App\Models\PurchaseOrderReturn::Statuses,
                'PURCHASE_ORDER_RETURN_REFUND_STATUSES' => \App\Models\PurchaseOrderReturn::RefundStatuses,
                'PURCHASE_ORDER_RETURN_REFUND_TYPES' => \App\Models\PurchaseOrderPayment::Types,
                'SALES_ORDER_STATUSES' => \App\Models\SalesOrder::Statuses,
                'SALES_ORDER_PAYMENT_STATUSES' => \App\Models\SalesOrder::PaymentStatuses,
                'SALES_ORDER_DELIVERY_STATUSES' => \App\Models\SalesOrder::DeliveryStatuses,
                'SALES_ORDER_PAYMENT_TYPES' => \App\Models\SalesOrderPayment::Types,
                'SALES_ORDER_RETURN_STATUSES' => \App\Models\SalesOrderReturn::Statuses,
                'SALES_ORDER_RETURN_REFUND_STATUSES' => \App\Models\SalesOrderReturn::RefundStatuses,
                'SALES_ORDER_RETURN_REFUND_TYPES' => \App\Models\SalesOrderPayment::Types,
            ]) ?>;
        window.CONSTANTS.USER_TYPE_SUPER_USER = "{{ \App\Models\User::Type_SuperUser }}";
        window.CONSTANTS.USER_TYPE_STANDARD_USER = "{{ \App\Models\User::Type_StandardUser }}";

        <?php
        /** @var \App\Models\User $user */
        $user = Illuminate\Support\Facades\Auth::user();
        $permissions = [];
        
        if ($user) {
            // Mengambil semua permission pengguna
            $permissions = $user->getAllPermissions()->pluck('name')->all();
        }
        ?>
        window.CONSTANTS.PERMISSIONS = <?= json_encode($permissions) ?>;
    </script>
@endsection
