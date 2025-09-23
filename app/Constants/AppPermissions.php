<?php

namespace App\Constants;

class AppPermissions
{
    // Konstanta untuk nama izin

    public const PRODUCT_LIST    = 'admin.product.index';
    public const PRODUCT_VIEW    = 'admin.product.detail';
    public const PRODUCT_CREATE  = 'admin.product.create';
    public const PRODUCT_EDIT    = 'admin.product.edit';
    public const PRODUCT_DELETE  = 'admin.product.delete';

    public const USER_LIST        = 'admin.user.list';
    public const USER_CREATE      = 'admin.user.create';
    public const USER_EDIT        = 'admin.user.edit';
    public const USER_DELETE      = 'admin.user.delete';
    public const USER_ASSIGN_ROLE = 'admin.user.assign_role';

    public const CASHIER_SESSION_LIST   = 'admin.cashier-session.list';
    public const CASHIER_SESSION_START  = 'admin.cashier-session.start';
    public const CASHIER_SESSION_END    = 'admin.cashier-session.end';
    public const CASHIER_SESSION_DETAIL = 'admin.cashier-session.detail';
    public const CASHIER_SESSION_DELETE = 'admin.cashier-session.delete';

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
            // Kategori Manajemen Produk
            'Manajemen Produk' => [
                ['name' => self::PRODUCT_LIST, 'label' => 'Lihat Daftar Produk'],
                ['name' => self::PRODUCT_VIEW, 'label' => 'Lihat Detail Produk'],
                ['name' => self::PRODUCT_CREATE, 'label' => 'Tambah Produk Baru'],
                ['name' => self::PRODUCT_EDIT, 'label' => 'Ubah Produk'],
                ['name' => self::PRODUCT_DELETE, 'label' => 'Hapus Produk'],
            ],

            'Manajemen Sesi Kasir' => [
                ['name' => self::CASHIER_SESSION_LIST, 'label' => 'Lihat Daftar Sesi Kasir'],
                ['name' => self::CASHIER_SESSION_START, 'label' => 'Memulai Sesi Kasir Baru'],
                ['name' => self::CASHIER_SESSION_END, 'label' => 'Mengakhiri Sesi Kasir'],
                ['name' => self::CASHIER_SESSION_DETAIL, 'label' => 'Lihat Detail Sesi Kasir'],
                ['name' => self::CASHIER_SESSION_DELETE, 'label' => 'Hapus Sesi Kasir'],
            ],

            // Kategori Manajemen Pengguna
            'Manajemen Pengguna' => [
                ['name' => self::USER_LIST, 'label' => 'Lihat Daftar Pengguna'],
                ['name' => self::USER_CREATE, 'label' => 'Tambah Pengguna Baru'],
                ['name' => self::USER_EDIT, 'label' => 'Ubah Pengguna'],
                ['name' => self::USER_DELETE, 'label' => 'Hapus Pengguna'],
                ['name' => self::USER_ASSIGN_ROLE, 'label' => 'Atur Peran Pengguna'],
            ],

            // Kategori Manajemen Keuangan
            'Manajemen Keuangan' => [
                ['name' => self::FINANCE_VIEW_REPORT, 'label' => 'Lihat Laporan Keuangan'],
                ['name' => self::FINANCE_MANAGE_ACCOUNT, 'label' => 'Kelola Akun Keuangan'],
            ],
        ];
    }
}
