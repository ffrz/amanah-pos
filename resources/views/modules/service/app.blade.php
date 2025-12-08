@extends('layouts.app')

@section('vite')
    @vite(['resources/js/modules/service/app.js', 'resources/css/app.css'])
@endsection

@section('scripts')
    <script>
        window.CONSTANTS = <?= json_encode([
                'USER_TYPES' => \App\Models\User::Types,
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
