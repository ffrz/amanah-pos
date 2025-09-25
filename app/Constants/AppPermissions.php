<?php

namespace App\Constants;

class AppPermissions
{
    public static function aliases(): array
    {
        return [
            'admin.sales-order.add'    => 'admin.sales-order.edit',
            'admin.sales-order.save'   => 'admin.sales-order.edit',
            'admin.sales-order.close'  => 'admin.sales-order.edit',
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
            'Manajemen Wallet Pelanggan' => [
                'admin.customer-wallet-transaction.index' => 'Lihat Daftar Transaksi E-wallet',
                'admin.customer-wallet-transaction.detail' => 'Lihat Detail Transaksi E-wallet',
                'admin.customer-wallet-transaction.add' => 'Tambah Transaksi E-wallet',
                'admin.customer-wallet-transaction.delete' => 'Hapus Transaksi E-wallet',
            ],
            'Manajemen Konfirmasi Wallet Pelanggan' => [
                'admin.customer-wallet-transaction-confirmation.index' => 'Lihat Daftar Konfirmasi E-wallet',
                'admin.customer-wallet-transaction-confirmation.detail' => 'Lihat Detail Konfirmasi E-wallet',
                'admin.customer-wallet-transaction-confirmation:accept' => 'Setujui Konfirmasi E-wallet',
                'admin.customer-wallet-transaction-confirmation:deny' => 'Tolak Konfirmasi E-wallet',
                'admin.customer-wallet-transaction-confirmation.delete' => 'Hapus Konfirmasi E-wallet',

                // 'admin.customer-wallet-transaction-confirmation.save' => 'Simpan Konfirmasi E-wallet',
                // 'admin.customer-wallet-transaction-confirmation.data' => 'Data Konfirmasi E-wallet',
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
            'Manajemen Biaya Operasional' => [
                'admin.operational-cost.index' => 'Lihat Daftar Biaya Operasional',
                'admin.operational-cost.detail' => 'Lihat Detail Biaya Operasional',
                'admin.operational-cost.add' => 'Tambah / Duplikat Biaya Operasional',
                'admin.operational-cost.edit' => 'Edit Biaya Operasional',
                // 'admin.operational-cost.save' => 'Simpan Biaya Operasional',
                'admin.operational-cost.delete' => 'Hapus Biaya Operasional',
                // 'admin.operational-cost.duplicate' => 'Duplikasi Biaya Operasional',
                // 'admin.operational-cost.data' => 'Data Biaya Operasional',
            ],
            'Manajemen Kategori Biaya Operasional' => [
                'admin.operational-cost-category.index' => 'Lihat Daftar Kategori Biaya Operasional',
                'admin.operational-cost-category.add' => 'Tambah / Duplikat Kategori Biaya Operasional',
                'admin.operational-cost-category.edit' => 'Edit Kategori Biaya Operasional',
                // 'admin.operational-cost-category.save' => 'Simpan Kategori Biaya Operasional',
                'admin.operational-cost-category.delete' => 'Hapus Kategori Biaya Operasional',
                // 'admin.operational-cost-category.duplicate' => 'Duplikasi Kategori Biaya Operasional',
                // 'admin.operational-cost-category.data' => 'Data Kategori Biaya Operasional',
            ],
            'Manajemen Pembelian' => [
                'admin.purchase-order.index' => 'Lihat Daftar Pesanan Pembelian',
                'admin.purchase-order.detail' => 'Lihat Detail Pesanan Pembelian',
                // 'admin.purchase-order.add' => 'Tambah Pesanan Pembelian',
                'admin.purchase-order.edit' => 'Membuat / Edit Pesanan Pembelian',
                // 'admin.purchase-order.save' => 'Simpan Pesanan Pembelian',
                'admin.purchase-order.delete' => 'Hapus Pesanan Pembelian',
                // 'admin.purchase-order.data' => 'Data Pesanan Pembelian',
            ],
            'Manajemen Penjualan' => [
                'admin.sales-order.index' => 'Lihat Daftar Pesanan Penjualan',
                'admin.sales-order.detail' => 'Lihat Detail Pesanan Penjualan',
                // 'admin.sales-order.add' => 'Tambah Pesanan Penjualan',
                'admin.sales-order.edit' => 'Membuat / Edit Pesanan Penjualan',
                // 'admin.sales-order.save' => 'Simpan Pesanan Penjualan',
                'admin.sales-order.delete' => 'Hapus Pesanan Penjualan',
                // 'admin.sales-order.data' => 'Data Pesanan Penjualan',
                // 'admin.sales-order.add-item' => 'Tambah Item Pesanan Penjualan',
                // 'admin.sales-order.remove-item' => 'Hapus Item Pesanan Penjualan',
                // 'admin.sales-order.update' => 'Perbarui Pesanan Penjualan',
                // 'admin.sales-order.update-item' => 'Perbarui Item Pesanan Penjualan',
                'admin.sales-order.cancel' => 'Batalkan Pesanan Penjualan',
                'admin.sales-order.close' => 'Tutup Pesanan Penjualan',
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
