<?php

namespace App\Constants;

class AppPermissions
{
    public const USER_LIST        = 'admin.user.list';
    public const USER_CREATE      = 'admin.user.create';
    public const USER_EDIT        = 'admin.user.edit';
    public const USER_DELETE      = 'admin.user.delete';
    public const USER_ASSIGN_ROLE = 'admin.user.assign-role';

    public const FINANCE_VIEW_REPORT    = 'finance.view_report';
    public const FINANCE_MANAGE_ACCOUNT = 'finance.manage_account';

    /**
     * Get all defined permissions grouped by category.
     *
     * @return array
     */
    public static function all(): array
    {
        return [

            'Manajemen Produk' => [
                ['name' => 'admin.product.index', 'label' => 'Lihat Daftar Produk'],
                ['name' => 'admin.product.detail', 'label' => 'Lihat Detail Produk'],
                ['name' => 'admin.product.add', 'label' => 'Tambah Produk Baru'],
                ['name' => 'admin.product.edit', 'label' => 'Ubah Produk'],
                ['name' => 'admin.product.delete', 'label' => 'Hapus Produk'],
                ['name' => 'admin.product:view-cost', 'label' => 'Lihat Modal'],
                ['name' => 'admin.product:view-supplier', 'label' => 'Lihat Pemasok'],
            ],

            'Manajemen Sesi Kasir' => [
                ['name' => 'admin.cashier-session.list', 'label' => 'Lihat Daftar Sesi Kasir'],
                ['name' => 'admin.cashier-session.start', 'label' => 'Memulai Sesi Kasir Baru'],
                ['name' => 'admin.cashier-session.end', 'label' => 'Mengakhiri Sesi Kasir'],
                ['name' => 'admin.cashier-session.detail', 'label' => 'Lihat Detail Sesi Kasir'],
                ['name' => 'admin.cashier-session.delete', 'label' => 'Hapus Sesi Kasir'],
            ],

            'Manajemen Pengguna' => [
                ['name' => 'admin.user.list', 'label' => 'Lihat Daftar Pengguna'],
                ['name' => 'admin.user.detail', 'label' => 'Lihat Detail Pengguna'],
                ['name' => 'admin.user.add', 'label' => 'Tambah Pengguna Baru'],
                ['name' => 'admin.user.edit', 'label' => 'Ubah Pengguna'],
                ['name' => 'admin.user.delete', 'label' => 'Hapus Pengguna'],
            ],

            'Manajemen Keuangan' => [
                ['name' => 'admin.finance-account.list', 'label' => 'Lihat Daftar Akun'],
                ['name' => 'admin.finance-account.detail', 'label' => 'Lihat Detail Akun'],
                ['name' => 'admin.finance-account.add', 'label' => 'Tambah Akun'],
                ['name' => 'admin.finance-account.edit', 'label' => 'Ubah Akun'],
                ['name' => 'admin.finance-account.delete', 'label' => 'Hapus Akun'],
            ],

            'Pengaturan' => [
                ['name' => 'admin.company-profile.edit', 'label' => 'Ubah Profil Perusahaan'],
            ],
        ];
    }
}
