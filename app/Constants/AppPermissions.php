<?php

namespace App\Constants;

class AppPermissions
{
    public static function aliases(): array
    {
        return [
            'admin.sales-order.add'    => 'admin.sales-order.edit',
            'admin.sales-order.save'   => 'admin.sales-order.edit',
        ];
    }
    /**
     * Get all defined permissions grouped by category.
     *
     * @return array
     */
    public static function all(): array
    {
        return [
            'Manajemen Sesi Kasir' => [
                'admin.cashier-session.index' => 'Lihat Daftar',
                'admin.cashier-session.detail' => 'Lihat Rincian',
                'admin.cashier-session.open' => 'Buka Sesi',
                'admin.cashier-session.close' => 'Tutup Sesi',
                'admin.cashier-session.delete' => 'Hapus',
            ],
            'Manajemen Terminal Kasir' => [
                'admin.cashier-terminal.index' => 'Lihat Daftar',
                'admin.cashier-terminal.detail' => 'Lihat Rincian',
                'admin.cashier-terminal.add' => 'Tambah',
                'admin.cashier-terminal.edit' => 'Edit',
                'admin.cashier-terminal.delete' => 'Hapus',
            ],
            'Manajemen Pelanggan' => [
                'admin.customer.index' => 'Lihat Daftar',
                'admin.customer.detail' => 'Lihat Rincian',
                'admin.customer.add' => 'Tambah / Duplikat',
                'admin.customer.edit' => 'Edit',
                'admin.customer.delete' => 'Hapus',
                'admin.customer.import' => 'Impor',
            ],
            'Manajemen Wallet Pelanggan' => [
                'admin.customer-wallet-transaction.index' => 'Lihat Daftar',
                'admin.customer-wallet-transaction.detail' => 'Lihat Rincian',
                'admin.customer-wallet-transaction.add' => 'Tambah',
                'admin.customer-wallet-transaction.delete' => 'Hapus',
            ],
            'Manajemen Konfirmasi Wallet Pelanggan' => [
                'admin.customer-wallet-transaction-confirmation.index' => 'Lihat Daftar',
                'admin.customer-wallet-transaction-confirmation.detail' => 'Lihat Rincian',
                'admin.customer-wallet-transaction-confirmation:accept' => 'Setujui',
                'admin.customer-wallet-transaction-confirmation:deny' => 'Tolak',
                'admin.customer-wallet-transaction-confirmation.delete' => 'Hapus',
            ],
            'Manajemen Produk' => [
                'admin.product.index' => 'Lihat Daftar',
                'admin.product.detail' => 'Lihat Rincian',
                'admin.product.add' => 'Tambah / Duplikat',
                'admin.product.edit' => 'Edit',
                'admin.product.delete' => 'Hapus',
                'admin.product.import' => 'Impor',
                'admin.product:view-cost' => 'Lihat Modal',
                'admin.product:view-supplier' => 'Lihat Supplier',
            ],
            'Manajemen Kategori Produk' => [
                'admin.product-category.index' => 'Lihat Daftar',
                'admin.product-category.add' => 'Tambah / Duplikat',
                'admin.product-category.edit' => 'Edit',
                'admin.product-category.delete' => 'Hapus',
            ],
            'Manajemen Stok' => [
                'admin.stock-movement.index' => 'Lihat Riwayat Pergerakan Stok',
                'admin.stock-adjustment.index' => 'Lihat Daftar Penyesuaian Stok',
                'admin.stock-adjustment.detail' => 'Lihat Rincian Penyesuaian Stok',
                'admin.stock-adjustment.save' => 'Tambah / Edit Penyesuaian Stok',
                'admin.stock-adjustment.delete' => 'Hapus Penyesuaian Stok',
            ],
            'Manajemen Akun Keuangan' => [
                'admin.finance-account.index' => 'Lihat Daftar',
                'admin.finance-account.detail' => 'Lihat Rincian',
                'admin.finance-account.add' => 'Tambah / Duplikat',
                'admin.finance-account.edit' => 'Edit',
                'admin.finance-account.delete' => 'Hapus',
            ],
            'Manajemen Transaksi Keuangan' => [
                'admin.finance-transaction.index' => 'Lihat Daftar',
                'admin.finance-transaction.detail' => 'Lihat Rincian',
                'admin.finance-transaction.add' => 'Tambah / Duplikat',
                'admin.finance-transaction.edit' => 'Edit',
                'admin.finance-transaction.delete' => 'Hapus',
            ],
            'Manajemen Biaya Operasional' => [
                'admin.operational-cost.index' => 'Lihat Daftar',
                'admin.operational-cost.detail' => 'Lihat Rincian',
                'admin.operational-cost.add' => 'Tambah / Duplikat',
                'admin.operational-cost.edit' => 'Edit',
                'admin.operational-cost.delete' => 'Hapus',
            ],
            'Manajemen Kategori Biaya Operasional' => [
                'admin.operational-cost-category.index' => 'Lihat Daftar',
                'admin.operational-cost-category.add' => 'Tambah / Duplikat',
                'admin.operational-cost-category.edit' => 'Edit',
                'admin.operational-cost-category.delete' => 'Hapus',
            ],
            'Manajemen Pembelian' => [
                'admin.purchase-order.index' => 'Lihat Daftar',
                'admin.purchase-order.detail' => 'Lihat Rincian',
                'admin.purchase-order.edit' => 'Buat / Edit',
                'admin.purchase-order.delete' => 'Hapus',
                'admin.purchase-order.cancel' => 'Batalkan',
                'admin.purchase-order.close' => 'Selesaikan',
                'admin.purchase-order.add-payment' => 'Tambah Pembayaran',
                'admin.purchase-order.delete-payment' => 'Hapus Pembayaran',
            ],
            'Manajemen Penjualan' => [
                'admin.sales-order.index' => 'Lihat Daftar',
                'admin.sales-order.detail' => 'Lihat Rincian',
                'admin.sales-order.edit' => 'Buat / Edit',
                'admin.sales-order.delete' => 'Hapus',
                'admin.sales-order.cancel' => 'Batalkan',
                'admin.sales-order.close' => 'Selesaikan',
                'admin.sales-order.add-payment' => 'Tambah Pembayaran',
                'admin.sales-order.delete-payment' => 'Hapus Pembayaran',
            ],
            'Manajemen Pemasok' => [
                'admin.supplier.index' => 'Lihat Daftar',
                'admin.supplier.detail' => 'Lihat Rincian',
                'admin.supplier.add' => 'Tambah / Duplikat',
                'admin.supplier.edit' => 'Edit',
                'admin.supplier.delete' => 'Hapus',
            ],
            'Manajemen Pengguna' => [
                'admin.user.index' => 'Lihat Daftar',
                'admin.user.detail' => 'Lihat Rincian',
                'admin.user.add' => 'Tambah / Duplikat',
                'admin.user.edit' => 'Edit',
                'admin.user.delete' => 'Hapus',
            ],
            'Manajemen Peran Pengguna' => [
                'admin.user-role.index' => 'Lihat Daftar',
                'admin.user-role.detail' => 'Lihat Rincian',
                'admin.user-role.add' => 'Tambah',
                'admin.user-role.edit' => 'Edit',
                'admin.user-role.delete' => 'Hapus',
            ],
            'Pengaturan' => [
                'admin.company-profile.edit' => 'Edit Profil Perusahaan',
            ],
        ];
    }
}
