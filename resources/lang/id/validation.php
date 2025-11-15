<?php

return [
    'required' => ':attribute harus diisi.',
    'email' => 'Format :attribute tidak valid.',
    'alpha_num' => 'Format :attribute tidak valid, gunakan hanya huruf dan angka.',
    'regex' => 'Format :attribute tidak valid.',
    'unique' => ':attribute sudah digunakan.',
    'numeric' => ':attribute tidak valid.',
    'current_password' => ':attribute tidak valid.',
    'different' => ':attribute tidak boleh sama.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'max' => [
        'string' => ':attribute terlalu panjang, maksimal :max karakter.',
    ],
    'min' => [
        'string' => ':attribute terlalu pendek, minimal :min karakter.',
        'numeric' => 'Bilangan tidak valid.',
    ],
    'gt' => [
        'numeric' => ':attribute harus lebih dari :value'
    ],

    // 'custom' => [
    //     'email' => [
    //         'required' => 'Alamat email harus diisi.',
    //     ],
    // ],
    'attributes' => [
        'username' => 'Username',
        'code' => 'Kode',
        'name' => 'Nama',
        'email' => 'Email',
        'phone' => 'No Telepon',
        'phone_1' => 'No Telepon',
        'phone_2' => 'No Telepon',
        'phone_3' => 'No Telepon',
        'role' => 'Role',
        'address' => 'Alamat',
        'date' => 'Tanggal',
        'description' => 'Deskripsi',
        'category_id' => 'Kategori',
        'account_id' => 'Akun',
        'notes' => 'Catatan',
        'type' => 'Jenis',
        'amount' => 'Jumlah',
        'customer_name' => 'Nama Pelanggan',
        'customer_phone' => 'No Telepon',
        'customer_address' => 'Alamat',
        'company_name' => 'Nama Perusahaan',
        'company_phone' => 'No Telepon',
        'company_address' => 'Alamat',
        'password' => 'Kata sandi',
        'current_password' => 'Kata sandi sekarang',
        'to_account_id' => 'Akun tujuan',
    ],
];
