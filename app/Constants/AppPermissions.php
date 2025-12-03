<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 *
 * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 *
 * See the LICENSE file in the project root for full license information.
 * Lihat file LICENSE di root proyek untuk informasi lisensi lengkap.
 *
 * GitHub: https://github.com/ffrz
 * Email: fahmifauzirahman@gmail.com
 */

namespace App\Constants;

class AppPermissions
{
    public static function aliases(): array
    {
        return [
            'admin.sales-order.add'  => 'admin.sales-order.edit',
            'admin.sales-order.save' => 'admin.sales-order.edit',
            'admin.sales-order-return.add'  => 'admin.sales-order-return.edit',
            'admin.sales-order-return.save' => 'admin.sales-order-return.edit',
            'admin.purchase-order.add'  => 'admin.purchase-order.edit',
            'admin.purchase-order.save' => 'admin.purchase-order.edit',
            'admin.purchase-order-return.add'  => 'admin.purchase-order-return.edit',
            'admin.purchase-order-return.save' => 'admin.purchase-order-return.edit',
            'admin.cashier-cash-drop.save'  => 'admin.cashier-cash-drop.add', /// ?????
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
                'admin.cashier-session.index' => 'Lihat Daftar Sesi Kasir',
                'admin.cashier-session.detail' => 'Lihat Detail Sesi Kasir',
                'admin.cashier-session.open' => 'Buka Sesi Kasir',
                'admin.cashier-session.close' => 'Tutup Sesi Kasir',
                'admin.cashier-session.delete' => 'Hapus Sesi Kasir',
                // 'admin.cashier-session.data' => 'Data Sesi Kasir',
            ],
            'Manajemen Terminal Kasir' => [
                'admin.cashier-terminal.index' => 'Lihat Daftar Terminal Kasir',
                'admin.cashier-terminal.detail' => 'Lihat Detail Terminal Kasir',
                'admin.cashier-terminal.add' => 'Tambah Terminal Kasir',
                'admin.cashier-terminal.edit' => 'Edit Terminal Kasir',
                'admin.cashier-terminal.delete' => 'Hapus Terminal Kasir',
                // 'admin.cashier-terminal.save' => 'Simpan Terminal Kasir',
                // 'admin.cashier-terminal.data' => 'Data Terminal Kasir',
            ],
            'Manajemen Setoran Kas' => [
                'admin.cashier-cash-drop.index' => 'Lihat Daftar Setoran Kas',
                'admin.cashier-cash-drop.detail' => 'Lihat Detail Setoran Kas',
                'admin.cashier-cash-drop.add' => 'Buat Pengajuan Setoran Kas', // Untuk Kasir
                'admin.cashier-cash-drop.confirm' => 'Konfirmasi Setoran Kas', // Untuk Supervisor/Owner
                'admin.cashier-cash-drop.delete' => 'Hapus Setoran Kas',
            ],

            'Manajemen Pelanggan' => [
                'admin.customer.index' => 'Lihat Daftar Pelanggan',
                'admin.customer.detail' => 'Lihat Detail Pelanggan',
                'admin.customer.add' => 'Tambah / Duplikat Pelanggan',
                'admin.customer.edit' => 'Edit Pelanggan',
                'admin.customer.delete' => 'Hapus Pelanggan',
                'admin.customer.import' => 'Impor Data Pelanggan',
                // 'admin.customer.save' => 'Simpan Pelanggan',
                // 'admin.customer.duplicate' => 'Duplikasi Pelanggan',
                // 'admin.customer.balance' => 'Saldo Pelanggan',
                // 'admin.customer.data' => 'Data Pelanggan',
            ],

            'Manajemen Utang / Piutang Pelanggan' => [
                'admin.customer-ledger.index'  => 'Lihat Daftar Transaksi',
                'admin.customer-ledger.detail' => 'Lihat Detail Transaksi',
                'admin.customer-ledger.add'    => 'Tambah Transaksi',
                'admin.customer-ledger.adjust' => 'Menyesuaikan Transaksi',
                'admin.customer-ledger.delete' => 'Hapus Transaksi',
            ],

            'Manajemen Deposit Pelanggan' => [
                'admin.customer-wallet-transaction.index' => 'Lihat Daftar Transaksi Deposit',
                'admin.customer-wallet-transaction.detail' => 'Lihat Detail Transaksi Deposit',
                'admin.customer-wallet-transaction.add' => 'Tambah Transaksi Deposit',
                'admin.customer-wallet-transaction.delete' => 'Hapus Transaksi Deposit',
            ],

            'Manajemen Konfirmasi Deposit Pelanggan' => [
                'admin.customer-wallet-transaction-confirmation.index' => 'Lihat Daftar Konfirmasi Deposit',
                'admin.customer-wallet-transaction-confirmation.detail' => 'Lihat Detail Konfirmasi Deposit',
                'admin.customer-wallet-transaction-confirmation:accept' => 'Setujui Konfirmasi Deposit',
                'admin.customer-wallet-transaction-confirmation:deny' => 'Tolak Konfirmasi Deposit',
                'admin.customer-wallet-transaction-confirmation.delete' => 'Hapus Konfirmasi Deposit',

                // 'admin.customer-wallet-transaction-confirmation.save' => 'Simpan Konfirmasi Deposit',
                // 'admin.customer-wallet-transaction-confirmation.data' => 'Data Konfirmasi Deposit',
            ],
            'Manajemen Deposit Supplier' => [
                'admin.supplier-wallet-transaction.index' => 'Lihat Daftar Transaksi Deposit',
                'admin.supplier-wallet-transaction.detail' => 'Lihat Detail Transaksi Deposit',
                'admin.supplier-wallet-transaction.add' => 'Tambah Transaksi Deposit',
                'admin.supplier-wallet-transaction.delete' => 'Hapus Transaksi Deposit',
            ],

            'Manajemen Utang / Piutang Supplier' => [
                'admin.supplier-ledger.index'  => 'Lihat Daftar Transaksi',
                'admin.supplier-ledger.detail' => 'Lihat Detail Transaksi',
                'admin.supplier-ledger.add'    => 'Tambah Transaksi',
                'admin.supplier-ledger.adjust' => 'Menyesuaikan Transaksi',
                'admin.supplier-ledger.delete' => 'Hapus Transaksi',
            ],

            'Manajemen Produk' => [
                'admin.product.index' => 'Lihat Daftar Produk',
                'admin.product.detail' => 'Lihat Detail Produk',
                'admin.product.add' => 'Tambah / Duplikat Produk',
                'admin.product.edit' => 'Edit Produk',
                'admin.product.delete' => 'Hapus Produk',
                'admin.product.import' => 'Impor Data Produk',
                'admin.product:view-cost' => 'Lihat Modal',
                'admin.product:view-supplier' => 'Lihat Supplier',
                // 'admin.product.data' => 'Data Produk',
                // 'admin.product.save' => 'Simpan Produk',
                // 'admin.product.duplicate' => 'Duplikasi Produk',
            ],
            'Manajemen Kategori Produk' => [
                'admin.product-category.index' => 'Lihat Daftar Kategori Produk',
                // 'admin.product-category.detail' => 'Lihat Detail Kategori Produk',
                'admin.product-category.add' => 'Tambah / Duplikat Kategori Produk',
                'admin.product-category.edit' => 'Edit Kategori Produk',
                'admin.product-category.delete' => 'Hapus Kategori Produk',
                // 'admin.product-category.save' => 'Simpan Kategori Produk',
                // 'admin.product-category.duplicate' => 'Duplikasi Kategori Produk',
                // 'admin.product-category.data' => 'Data Kategori Produk',
            ],
            'Manajemen Stok' => [
                'admin.stock-movement.index' => 'Lihat Daftar Pergerakan Stok',
                'admin.stock-adjustment.index' => 'Lihat Daftar Penyesuaian Stok',
                'admin.stock-adjustment.detail' => 'Lihat Detail Penyesuaian Stok',
                'admin.stock-adjustment.save' => 'Simpan Penyesuaian Stok',
                'admin.stock-adjustment.delete' => 'Hapus Penyesuaian Stok',
                // 'admin.stock-adjustment.create' => ' Penyesuaian Stok',
                // 'admin.stock-adjustment.editor' => 'Editor Penyesuaian Stok',
                // 'admin.stock-adjustment.data' => 'Data Penyesuaian Stok',
                // 'admin.stock-movement.data' => 'Data Pergerakan Stok',
            ],
            'Manajemen Akun Keuangan' => [
                'admin.finance-account.index' => 'Lihat Daftar Akun Keuangan',
                'admin.finance-account.detail' => 'Lihat Detail Akun Keuangan',
                'admin.finance-account.add' => 'Tambah / Duplikat Akun Keuangan',
                'admin.finance-account.edit' => 'Edit Akun Keuangan',
                // 'admin.finance-account.save' => 'Simpan Akun Keuangan',
                'admin.finance-account.delete' => 'Hapus Akun Keuangan',
                // 'admin.finance-account.duplicate' => 'Duplikasi Akun Keuangan',
                // 'admin.finance-account.data' => 'Data Akun Keuangan',
            ],
            'Manajemen Transaksi Keuangan' => [
                'admin.finance-transaction.index' => 'Lihat Daftar Transaksi Keuangan',
                'admin.finance-transaction.detail' => 'Lihat Detail Transaksi Keuangan',
                'admin.finance-transaction.add' => 'Tambah / Duplikat Transaksi Keuangan',
                'admin.finance-transaction.edit' => 'Edit Transaksi Keuangan',
                // 'admin.finance-transaction.save' => 'Simpan Transaksi Keuangan',
                'admin.finance-transaction.delete' => 'Hapus Transaksi Keuangan',
                // 'admin.finance-transaction.data' => 'Data Transaksi Keuangan',
            ],
            'Manajemen Kategori Transaksi Keuangan' => [
                'admin.finance-transaction-category.index' => 'Lihat Daftar Kategori Transaksi',
                'admin.finance-transaction-category.add' => 'Tambah / Duplikat Kategori Transaksi',
                'admin.finance-transaction-category.edit' => 'Edit Kategori Transaksi',
                'admin.finance-transaction-category.delete' => 'Hapus Kategori Transaksi',
            ],

            'Manajemen Biaya Operasional' => [
                'admin.operational-cost.index' => 'Lihat Daftar Biaya Operasional',
                'admin.operational-cost.detail' => 'Lihat Detail Biaya Operasional',
                'admin.operational-cost.add' => 'Tambah / Duplikat Biaya Operasional',
                'admin.operational-cost.edit' => 'Edit Biaya Operasional',
                'admin.operational-cost.delete' => 'Hapus Biaya Operasional',
            ],
            'Manajemen Kategori Biaya Operasional' => [
                'admin.operational-cost-category.index' => 'Lihat Daftar Kategori Biaya Operasional',
                'admin.operational-cost-category.add' => 'Tambah / Duplikat Kategori Biaya Operasional',
                'admin.operational-cost-category.edit' => 'Edit Kategori Biaya Operasional',
                'admin.operational-cost-category.delete' => 'Hapus Kategori Biaya Operasional',
            ],
            'Manajemen Pembelian' => [
                'admin.purchase-order.index' => 'Lihat Daftar Pembelian',
                'admin.purchase-order.detail' => 'Lihat Detail Pembelian',
                'admin.purchase-order.edit' => 'Membuat / Edit Pembelian',
                'admin.purchase-order.delete' => 'Hapus Pembelian',
                'admin.purchase-order.cancel' => 'Batalkan Pembelian',
                'admin.purchase-order.close' => 'Tutup Pembelian',
                'admin.purchase-order.add-payment' => 'Tambah Pembayaran',
                'admin.purchase-order.delete-payment' => 'Hapus Pembayaran',
            ],
            'Manajemen Retur Pembelian' => [
                'admin.purchase-order-return.index' => 'Lihat Daftar Retur Pembelian',
                'admin.purchase-order-return.detail' => 'Lihat Detail Retur Pembelian',
                'admin.purchase-order-return.edit' => 'Membuat / Edit Retur Pembelian',
                'admin.purchase-order-return.delete' => 'Hapus Retur Pembelian',
                'admin.purchase-order-return.cancel' => 'Batalkan Retur Pembelian',
                'admin.purchase-order-return.close' => 'Tutup Retur Pembelian',
                'admin.purchase-order-return.add-refund' => 'Tambah Refund Pembayaran',
                'admin.purchase-order-return.delete-refund' => 'Hapus Refund Pembayaran',
            ],
            'Manajemen Penjualan' => [
                'admin.sales-order.index' => 'Lihat Daftar Penjualan',
                'admin.sales-order.detail' => 'Lihat Detail Penjualan',
                'admin.sales-order.edit' => 'Membuat / Edit Penjualan',
                'admin.sales-order.delete' => 'Hapus Penjualan',
                'admin.sales-order.cancel' => 'Batalkan Penjualan',
                'admin.sales-order.close' => 'Tutup Penjualan',
                'admin.sales-order.add-payment' => 'Tambah Pembayaran',
                'admin.sales-order.delete-payment' => 'Hapus Pembayaran',
            ],
            'Manajemen Retur Penjual' => [
                'admin.sales-order-return.index' => 'Lihat Daftar Retur Penjualan',
                'admin.sales-order-return.detail' => 'Lihat Detail Retur Penjualan',
                'admin.sales-order-return.edit' => 'Membuat / Edit Retur Penjualan',
                'admin.sales-order-return.delete' => 'Hapus Retur Penjualan',
                'admin.sales-order-return.cancel' => 'Batalkan Retur Penjualan',
                'admin.sales-order-return.close' => 'Tutup Retur Penjualan',
                'admin.sales-order-return.add-refund' => 'Tambah Refund Pembayaran',
                'admin.sales-order-return.delete-refund' => 'Hapus Refund Pembayaran',
            ],
            'Manajemen Pemasok' => [
                'admin.supplier.index' => 'Lihat Daftar Pemasok',
                'admin.supplier.detail' => 'Lihat Detail Pemasok',
                'admin.supplier.add' => 'Tambah / Duplikat Pemasok',
                'admin.supplier.edit' => 'Edit Pemasok',
                // 'admin.supplier.save' => 'Simpan Pemasok',
                'admin.supplier.delete' => 'Hapus Pemasok',
                // 'admin.supplier.duplicate' => 'Duplikasi Pemasok',
                // 'admin.supplier.data' => 'Data Pemasok',
            ],
            'Manajemen Pengguna' => [
                'admin.user.index' => 'Lihat Daftar Pengguna',
                'admin.user.detail' => 'Lihat Detail Pengguna',
                'admin.user.add' => 'Tambah / Duplikat Pengguna',
                'admin.user.edit' => 'Edit Pengguna',
                // 'admin.user.save' => 'Simpan Pengguna',
                'admin.user.delete' => 'Hapus Pengguna',
                // 'admin.user.duplicate' => 'Duplikasi Pengguna',
                // 'admin.user.data' => 'Data Pengguna',
            ],
            'Manajemen Peran Pengguna' => [
                'admin.user-role.index' => 'Lihat Daftar Peran Pengguna',
                'admin.user-role.detail' => 'Lihat Detail Peran Pengguna',
                'admin.user-role.add' => 'Tambah Peran Pengguna',
                'admin.user-role.edit' => 'Edit Peran Pengguna',
                // 'admin.user-role.save' => 'Simpan Peran Pengguna',
                'admin.user-role.delete' => 'Hapus Peran Pengguna',
                // 'admin.user-role.data' => 'Data Peran Pengguna',
            ],
            'Pengaturan' => [
                // 'admin.user-profile.edit' => 'Edit Profil Pengguna',
                // 'admin.user-profile.update' => 'Perbarui Profil Pengguna',
                // 'admin.user-profile.update-password' => 'Perbarui Kata Sandi',

                'admin.company-profile.edit' => 'Edit Profil Perusahaan',
            ],
        ];
    }
}
