@extends('layouts.app')

@section('vite')
    @vite(['resources/js/modules/customer/app.js', 'resources/css/app.css'])
@endsection

@section('scripts')
  <script>
    window.CONSTANTS = <?= json_encode([
      'CUSTOMER_TYPES' => \App\Models\Customer::Types,
      'CUSTOMER_WALLET_TRANSACTION_TYPES' => \App\Models\CustomerWalletTransaction::Types,
      'CUSTOMER_WALLET_TRANSACTION_CONFIRMATION_STATUSES' => \App\Models\CustomerWalletTransactionConfirmation::Statuses,
    ]) ?>;
  </script>
@endsection