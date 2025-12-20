-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 20, 2025 at 04:18 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shiftech_amanah_pos`
--

-- --------------------------------------------------------

--
-- Table structure for table `acl_model_has_permissions`
--

CREATE TABLE `acl_model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `acl_model_has_roles`
--

CREATE TABLE `acl_model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `acl_model_has_roles`
--

INSERT INTO `acl_model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 2);

-- --------------------------------------------------------

--
-- Table structure for table `acl_permissions`
--

CREATE TABLE `acl_permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `acl_permissions`
--

INSERT INTO `acl_permissions` (`id`, `name`, `guard_name`, `label`, `category`, `created_at`, `updated_at`) VALUES
(1, 'admin.cashier-session.index', 'web', 'Lihat Daftar Sesi Kasir', 'Manajemen Sesi Kasir', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(2, 'admin.cashier-session.detail', 'web', 'Lihat Detail Sesi Kasir', 'Manajemen Sesi Kasir', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(3, 'admin.cashier-session.open', 'web', 'Buka Sesi Kasir', 'Manajemen Sesi Kasir', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(4, 'admin.cashier-session.close', 'web', 'Tutup Sesi Kasir', 'Manajemen Sesi Kasir', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(5, 'admin.cashier-session.delete', 'web', 'Hapus Sesi Kasir', 'Manajemen Sesi Kasir', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(6, 'admin.cashier-terminal.index', 'web', 'Lihat Daftar Terminal Kasir', 'Manajemen Terminal Kasir', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(7, 'admin.cashier-terminal.detail', 'web', 'Lihat Detail Terminal Kasir', 'Manajemen Terminal Kasir', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(8, 'admin.cashier-terminal.add', 'web', 'Tambah Terminal Kasir', 'Manajemen Terminal Kasir', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(9, 'admin.cashier-terminal.edit', 'web', 'Edit Terminal Kasir', 'Manajemen Terminal Kasir', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(10, 'admin.cashier-terminal.delete', 'web', 'Hapus Terminal Kasir', 'Manajemen Terminal Kasir', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(11, 'admin.cashier-cash-drop.index', 'web', 'Lihat Daftar Setoran Kas', 'Manajemen Setoran Kas', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(12, 'admin.cashier-cash-drop.detail', 'web', 'Lihat Detail Setoran Kas', 'Manajemen Setoran Kas', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(13, 'admin.cashier-cash-drop.add', 'web', 'Buat Pengajuan Setoran Kas', 'Manajemen Setoran Kas', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(14, 'admin.cashier-cash-drop.confirm', 'web', 'Konfirmasi Setoran Kas', 'Manajemen Setoran Kas', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(15, 'admin.cashier-cash-drop.delete', 'web', 'Hapus Setoran Kas', 'Manajemen Setoran Kas', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(16, 'admin.customer.index', 'web', 'Lihat Daftar Pelanggan', 'Manajemen Pelanggan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(17, 'admin.customer.detail', 'web', 'Lihat Detail Pelanggan', 'Manajemen Pelanggan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(18, 'admin.customer.add', 'web', 'Tambah / Duplikat Pelanggan', 'Manajemen Pelanggan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(19, 'admin.customer.edit', 'web', 'Edit Pelanggan', 'Manajemen Pelanggan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(20, 'admin.customer.delete', 'web', 'Hapus Pelanggan', 'Manajemen Pelanggan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(21, 'admin.customer.import', 'web', 'Impor Data Pelanggan', 'Manajemen Pelanggan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(22, 'admin.customer-ledger.index', 'web', 'Lihat Daftar Transaksi', 'Manajemen Utang / Piutang Pelanggan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(23, 'admin.customer-ledger.detail', 'web', 'Lihat Detail Transaksi', 'Manajemen Utang / Piutang Pelanggan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(24, 'admin.customer-ledger.add', 'web', 'Tambah Transaksi', 'Manajemen Utang / Piutang Pelanggan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(25, 'admin.customer-ledger.adjust', 'web', 'Menyesuaikan Transaksi', 'Manajemen Utang / Piutang Pelanggan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(26, 'admin.customer-ledger.delete', 'web', 'Hapus Transaksi', 'Manajemen Utang / Piutang Pelanggan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(27, 'admin.customer-wallet-transaction.index', 'web', 'Lihat Daftar Transaksi Deposit', 'Manajemen Deposit Pelanggan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(28, 'admin.customer-wallet-transaction.detail', 'web', 'Lihat Detail Transaksi Deposit', 'Manajemen Deposit Pelanggan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(29, 'admin.customer-wallet-transaction.add', 'web', 'Tambah Transaksi Deposit', 'Manajemen Deposit Pelanggan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(30, 'admin.customer-wallet-transaction.delete', 'web', 'Hapus Transaksi Deposit', 'Manajemen Deposit Pelanggan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(31, 'admin.customer-wallet-transaction-confirmation.index', 'web', 'Lihat Daftar Konfirmasi Deposit', 'Manajemen Konfirmasi Deposit Pelanggan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(32, 'admin.customer-wallet-transaction-confirmation.detail', 'web', 'Lihat Detail Konfirmasi Deposit', 'Manajemen Konfirmasi Deposit Pelanggan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(33, 'admin.customer-wallet-transaction-confirmation:accept', 'web', 'Setujui Konfirmasi Deposit', 'Manajemen Konfirmasi Deposit Pelanggan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(34, 'admin.customer-wallet-transaction-confirmation:deny', 'web', 'Tolak Konfirmasi Deposit', 'Manajemen Konfirmasi Deposit Pelanggan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(35, 'admin.customer-wallet-transaction-confirmation.delete', 'web', 'Hapus Konfirmasi Deposit', 'Manajemen Konfirmasi Deposit Pelanggan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(36, 'admin.supplier-wallet-transaction.index', 'web', 'Lihat Daftar Transaksi Deposit', 'Manajemen Deposit Supplier', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(37, 'admin.supplier-wallet-transaction.detail', 'web', 'Lihat Detail Transaksi Deposit', 'Manajemen Deposit Supplier', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(38, 'admin.supplier-wallet-transaction.add', 'web', 'Tambah Transaksi Deposit', 'Manajemen Deposit Supplier', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(39, 'admin.supplier-wallet-transaction.delete', 'web', 'Hapus Transaksi Deposit', 'Manajemen Deposit Supplier', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(40, 'admin.supplier-ledger.index', 'web', 'Lihat Daftar Transaksi', 'Manajemen Utang / Piutang Supplier', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(41, 'admin.supplier-ledger.detail', 'web', 'Lihat Detail Transaksi', 'Manajemen Utang / Piutang Supplier', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(42, 'admin.supplier-ledger.add', 'web', 'Tambah Transaksi', 'Manajemen Utang / Piutang Supplier', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(43, 'admin.supplier-ledger.adjust', 'web', 'Menyesuaikan Transaksi', 'Manajemen Utang / Piutang Supplier', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(44, 'admin.supplier-ledger.delete', 'web', 'Hapus Transaksi', 'Manajemen Utang / Piutang Supplier', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(45, 'admin.product.index', 'web', 'Lihat Daftar Produk', 'Manajemen Produk', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(46, 'admin.product.detail', 'web', 'Lihat Detail Produk', 'Manajemen Produk', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(47, 'admin.product.add', 'web', 'Tambah / Duplikat Produk', 'Manajemen Produk', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(48, 'admin.product.edit', 'web', 'Edit Produk', 'Manajemen Produk', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(49, 'admin.product.delete', 'web', 'Hapus Produk', 'Manajemen Produk', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(50, 'admin.product.import', 'web', 'Impor Data Produk', 'Manajemen Produk', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(51, 'admin.product:view-cost', 'web', 'Lihat Modal', 'Manajemen Produk', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(52, 'admin.product:view-supplier', 'web', 'Lihat Supplier', 'Manajemen Produk', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(53, 'admin.product-category.index', 'web', 'Lihat Daftar Kategori Produk', 'Manajemen Kategori Produk', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(54, 'admin.product-category.add', 'web', 'Tambah / Duplikat Kategori Produk', 'Manajemen Kategori Produk', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(55, 'admin.product-category.edit', 'web', 'Edit Kategori Produk', 'Manajemen Kategori Produk', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(56, 'admin.product-category.delete', 'web', 'Hapus Kategori Produk', 'Manajemen Kategori Produk', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(57, 'admin.stock-movement.index', 'web', 'Lihat Daftar Pergerakan Stok', 'Manajemen Stok', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(58, 'admin.stock-adjustment.index', 'web', 'Lihat Daftar Penyesuaian Stok', 'Manajemen Stok', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(59, 'admin.stock-adjustment.detail', 'web', 'Lihat Detail Penyesuaian Stok', 'Manajemen Stok', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(60, 'admin.stock-adjustment.save', 'web', 'Simpan Penyesuaian Stok', 'Manajemen Stok', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(61, 'admin.stock-adjustment.delete', 'web', 'Hapus Penyesuaian Stok', 'Manajemen Stok', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(62, 'admin.finance-account.index', 'web', 'Lihat Daftar Akun Keuangan', 'Manajemen Akun Keuangan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(63, 'admin.finance-account.detail', 'web', 'Lihat Detail Akun Keuangan', 'Manajemen Akun Keuangan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(64, 'admin.finance-account.add', 'web', 'Tambah / Duplikat Akun Keuangan', 'Manajemen Akun Keuangan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(65, 'admin.finance-account.edit', 'web', 'Edit Akun Keuangan', 'Manajemen Akun Keuangan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(66, 'admin.finance-account.delete', 'web', 'Hapus Akun Keuangan', 'Manajemen Akun Keuangan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(67, 'admin.finance-transaction.index', 'web', 'Lihat Daftar Transaksi Keuangan', 'Manajemen Transaksi Keuangan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(68, 'admin.finance-transaction.detail', 'web', 'Lihat Detail Transaksi Keuangan', 'Manajemen Transaksi Keuangan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(69, 'admin.finance-transaction.add', 'web', 'Tambah / Duplikat Transaksi Keuangan', 'Manajemen Transaksi Keuangan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(70, 'admin.finance-transaction.edit', 'web', 'Edit Transaksi Keuangan', 'Manajemen Transaksi Keuangan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(71, 'admin.finance-transaction.delete', 'web', 'Hapus Transaksi Keuangan', 'Manajemen Transaksi Keuangan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(72, 'admin.finance-transaction-category.index', 'web', 'Lihat Daftar Kategori Transaksi', 'Manajemen Kategori Transaksi Keuangan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(73, 'admin.finance-transaction-category.add', 'web', 'Tambah / Duplikat Kategori Transaksi', 'Manajemen Kategori Transaksi Keuangan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(74, 'admin.finance-transaction-category.edit', 'web', 'Edit Kategori Transaksi', 'Manajemen Kategori Transaksi Keuangan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(75, 'admin.finance-transaction-category.delete', 'web', 'Hapus Kategori Transaksi', 'Manajemen Kategori Transaksi Keuangan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(76, 'admin.operational-cost.index', 'web', 'Lihat Daftar Biaya Operasional', 'Manajemen Biaya Operasional', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(77, 'admin.operational-cost.detail', 'web', 'Lihat Detail Biaya Operasional', 'Manajemen Biaya Operasional', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(78, 'admin.operational-cost.add', 'web', 'Tambah / Duplikat Biaya Operasional', 'Manajemen Biaya Operasional', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(79, 'admin.operational-cost.edit', 'web', 'Edit Biaya Operasional', 'Manajemen Biaya Operasional', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(80, 'admin.operational-cost.delete', 'web', 'Hapus Biaya Operasional', 'Manajemen Biaya Operasional', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(81, 'admin.operational-cost-category.index', 'web', 'Lihat Daftar Kategori Biaya Operasional', 'Manajemen Kategori Biaya Operasional', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(82, 'admin.operational-cost-category.add', 'web', 'Tambah / Duplikat Kategori Biaya Operasional', 'Manajemen Kategori Biaya Operasional', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(83, 'admin.operational-cost-category.edit', 'web', 'Edit Kategori Biaya Operasional', 'Manajemen Kategori Biaya Operasional', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(84, 'admin.operational-cost-category.delete', 'web', 'Hapus Kategori Biaya Operasional', 'Manajemen Kategori Biaya Operasional', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(85, 'admin.purchase-order.index', 'web', 'Lihat Daftar Pembelian', 'Manajemen Pembelian', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(86, 'admin.purchase-order.detail', 'web', 'Lihat Detail Pembelian', 'Manajemen Pembelian', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(87, 'admin.purchase-order.edit', 'web', 'Membuat / Edit Pembelian', 'Manajemen Pembelian', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(88, 'admin.purchase-order.delete', 'web', 'Hapus Pembelian', 'Manajemen Pembelian', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(89, 'admin.purchase-order.cancel', 'web', 'Batalkan Pembelian', 'Manajemen Pembelian', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(90, 'admin.purchase-order.close', 'web', 'Tutup Pembelian', 'Manajemen Pembelian', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(91, 'admin.purchase-order.add-payment', 'web', 'Tambah Pembayaran', 'Manajemen Pembelian', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(92, 'admin.purchase-order.delete-payment', 'web', 'Hapus Pembayaran', 'Manajemen Pembelian', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(93, 'admin.purchase-order-return.index', 'web', 'Lihat Daftar Retur Pembelian', 'Manajemen Retur Pembelian', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(94, 'admin.purchase-order-return.detail', 'web', 'Lihat Detail Retur Pembelian', 'Manajemen Retur Pembelian', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(95, 'admin.purchase-order-return.edit', 'web', 'Membuat / Edit Retur Pembelian', 'Manajemen Retur Pembelian', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(96, 'admin.purchase-order-return.delete', 'web', 'Hapus Retur Pembelian', 'Manajemen Retur Pembelian', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(97, 'admin.purchase-order-return.cancel', 'web', 'Batalkan Retur Pembelian', 'Manajemen Retur Pembelian', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(98, 'admin.purchase-order-return.close', 'web', 'Tutup Retur Pembelian', 'Manajemen Retur Pembelian', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(99, 'admin.purchase-order-return.add-refund', 'web', 'Tambah Refund Pembayaran', 'Manajemen Retur Pembelian', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(100, 'admin.purchase-order-return.delete-refund', 'web', 'Hapus Refund Pembayaran', 'Manajemen Retur Pembelian', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(101, 'admin.sales-order.index', 'web', 'Lihat Daftar Penjualan', 'Manajemen Penjualan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(102, 'admin.sales-order.detail', 'web', 'Lihat Detail Penjualan', 'Manajemen Penjualan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(103, 'admin.sales-order.edit', 'web', 'Membuat / Edit Penjualan', 'Manajemen Penjualan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(104, 'admin.sales-order.editor:edit-price', 'web', 'Edit Harga Jual', 'Manajemen Penjualan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(105, 'admin.sales-order.delete', 'web', 'Hapus Penjualan', 'Manajemen Penjualan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(106, 'admin.sales-order.cancel', 'web', 'Batalkan Penjualan', 'Manajemen Penjualan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(107, 'admin.sales-order.close', 'web', 'Tutup Penjualan', 'Manajemen Penjualan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(108, 'admin.sales-order.add-payment', 'web', 'Tambah Pembayaran', 'Manajemen Penjualan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(109, 'admin.sales-order.delete-payment', 'web', 'Hapus Pembayaran', 'Manajemen Penjualan', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(110, 'admin.sales-order-return.index', 'web', 'Lihat Daftar Retur Penjualan', 'Manajemen Retur Penjual', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(111, 'admin.sales-order-return.detail', 'web', 'Lihat Detail Retur Penjualan', 'Manajemen Retur Penjual', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(112, 'admin.sales-order-return.edit', 'web', 'Membuat / Edit Retur Penjualan', 'Manajemen Retur Penjual', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(113, 'admin.sales-order-return.delete', 'web', 'Hapus Retur Penjualan', 'Manajemen Retur Penjual', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(114, 'admin.sales-order-return.cancel', 'web', 'Batalkan Retur Penjualan', 'Manajemen Retur Penjual', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(115, 'admin.sales-order-return.close', 'web', 'Tutup Retur Penjualan', 'Manajemen Retur Penjual', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(116, 'admin.sales-order-return.add-refund', 'web', 'Tambah Refund Pembayaran', 'Manajemen Retur Penjual', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(117, 'admin.sales-order-return.delete-refund', 'web', 'Hapus Refund Pembayaran', 'Manajemen Retur Penjual', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(118, 'admin.supplier.index', 'web', 'Lihat Daftar Pemasok', 'Manajemen Pemasok', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(119, 'admin.supplier.detail', 'web', 'Lihat Detail Pemasok', 'Manajemen Pemasok', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(120, 'admin.supplier.add', 'web', 'Tambah / Duplikat Pemasok', 'Manajemen Pemasok', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(121, 'admin.supplier.edit', 'web', 'Edit Pemasok', 'Manajemen Pemasok', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(122, 'admin.supplier.delete', 'web', 'Hapus Pemasok', 'Manajemen Pemasok', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(123, 'admin.user.index', 'web', 'Lihat Daftar Pengguna', 'Manajemen Pengguna', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(124, 'admin.user.detail', 'web', 'Lihat Detail Pengguna', 'Manajemen Pengguna', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(125, 'admin.user.add', 'web', 'Tambah / Duplikat Pengguna', 'Manajemen Pengguna', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(126, 'admin.user.edit', 'web', 'Edit Pengguna', 'Manajemen Pengguna', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(127, 'admin.user.delete', 'web', 'Hapus Pengguna', 'Manajemen Pengguna', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(128, 'admin.user-role.index', 'web', 'Lihat Daftar Peran Pengguna', 'Manajemen Peran Pengguna', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(129, 'admin.user-role.detail', 'web', 'Lihat Detail Peran Pengguna', 'Manajemen Peran Pengguna', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(130, 'admin.user-role.add', 'web', 'Tambah Peran Pengguna', 'Manajemen Peran Pengguna', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(131, 'admin.user-role.edit', 'web', 'Edit Peran Pengguna', 'Manajemen Peran Pengguna', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(132, 'admin.user-role.delete', 'web', 'Hapus Peran Pengguna', 'Manajemen Peran Pengguna', '2025-12-20 04:17:40', '2025-12-20 04:17:40'),
(133, 'admin.company-profile.edit', 'web', 'Edit Profil Perusahaan', 'Pengaturan', '2025-12-20 04:17:40', '2025-12-20 04:17:40');

-- --------------------------------------------------------

--
-- Table structure for table `acl_roles`
--

CREATE TABLE `acl_roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `acl_roles`
--

INSERT INTO `acl_roles` (`id`, `name`, `description`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Kasir', NULL, 'web', '2025-12-20 04:17:40', '2025-12-20 04:17:40');

-- --------------------------------------------------------

--
-- Table structure for table `acl_role_has_permissions`
--

CREATE TABLE `acl_role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `acl_role_has_permissions`
--

INSERT INTO `acl_role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(6, 1),
(7, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(45, 1),
(46, 1),
(53, 1),
(57, 1),
(67, 1),
(68, 1),
(69, 1),
(76, 1),
(77, 1),
(78, 1),
(81, 1),
(82, 1),
(101, 1),
(102, 1),
(103, 1),
(124, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cashier_cash_drops`
--

CREATE TABLE `cashier_cash_drops` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datetime` datetime NOT NULL,
  `cashier_id` bigint UNSIGNED NOT NULL,
  `terminal_id` bigint UNSIGNED DEFAULT NULL,
  `cashier_session_id` bigint UNSIGNED DEFAULT NULL,
  `source_finance_account_id` bigint UNSIGNED NOT NULL,
  `target_finance_account_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cashier_sessions`
--

CREATE TABLE `cashier_sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `cashier_terminal_id` bigint UNSIGNED NOT NULL,
  `opening_balance` decimal(15,2) NOT NULL,
  `closing_balance` decimal(15,2) DEFAULT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT '0',
  `opened_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `opening_notes` text COLLATE utf8mb4_unicode_ci,
  `closing_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cashier_session_transactions`
--

CREATE TABLE `cashier_session_transactions` (
  `cashier_session_id` bigint UNSIGNED NOT NULL,
  `finance_transaction_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cashier_terminals`
--

CREATE TABLE `cashier_terminals` (
  `id` bigint UNSIGNED NOT NULL,
  `finance_account_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cashier_terminals`
--

INSERT INTO `cashier_terminals` (`id`, `finance_account_id`, `name`, `location`, `notes`, `active`, `created_at`, `deleted_at`, `created_by`, `deleted_by`) VALUES
(1, 2, 'Kasir Toko 1', 'Toko', 'Auto generated cashier terminal', 1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint UNSIGNED NOT NULL,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `phone` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `address` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `wallet_balance` decimal(15,0) NOT NULL DEFAULT '0',
  `balance` decimal(15,0) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `credit_limit` decimal(15,0) NOT NULL DEFAULT '0',
  `credit_allowed` tinyint(1) NOT NULL DEFAULT '1',
  `default_price_type` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_login_datetime` datetime DEFAULT NULL,
  `last_activity_description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `last_activity_datetime` datetime DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_ledgers`
--

CREATE TABLE `customer_ledgers` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `finance_account_id` bigint UNSIGNED DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_id` bigint UNSIGNED DEFAULT NULL,
  `datetime` datetime NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `running_balance` decimal(12,2) DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_password_reset_tokens`
--

CREATE TABLE `customer_password_reset_tokens` (
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_sessions`
--

CREATE TABLE `customer_sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_account_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_wallet_transactions`
--

CREATE TABLE `customer_wallet_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `finance_account_id` bigint UNSIGNED DEFAULT NULL,
  `customer_id` bigint UNSIGNED DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ref_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_id` bigint UNSIGNED DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_wallet_trx_confirmations`
--

CREATE TABLE `customer_wallet_trx_confirmations` (
  `id` bigint UNSIGNED NOT NULL,
  `finance_account_id` bigint UNSIGNED DEFAULT NULL,
  `customer_id` bigint UNSIGNED DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datetime` datetime DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_versions`
--

CREATE TABLE `document_versions` (
  `id` bigint UNSIGNED NOT NULL,
  `document_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_id` bigint UNSIGNED NOT NULL,
  `version` int UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `changelog` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Deskripsi singkat perubahan (opsional)',
  `is_deleted` tinyint(1) NOT NULL,
  `data` json NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_accounts`
--

CREATE TABLE `finance_accounts` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bank` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `holder` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `balance` decimal(15,0) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `show_in_pos_payment` tinyint(1) NOT NULL DEFAULT '0',
  `show_in_purchasing_payment` tinyint(1) NOT NULL DEFAULT '0',
  `has_wallet_access` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `show_in_cashier_cash_drop` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `finance_accounts`
--

INSERT INTO `finance_accounts` (`id`, `name`, `type`, `bank`, `number`, `holder`, `balance`, `active`, `show_in_pos_payment`, `show_in_purchasing_payment`, `has_wallet_access`, `notes`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`, `show_in_cashier_cash_drop`) VALUES
(1, 'Kas Tunai 1', 'petty_cash', '', '', '', 0, 1, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(2, 'Kas Kasir 1', 'cashier_cash', '', '', '', 0, 1, 0, 0, 0, NULL, '2025-12-20 11:17:41', NULL, NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `finance_transactions`
--

CREATE TABLE `finance_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `account_id` bigint UNSIGNED DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ref_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_id` bigint UNSIGNED DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_transactions_has_tags`
--

CREATE TABLE `finance_transactions_has_tags` (
  `finance_transaction_id` bigint UNSIGNED NOT NULL,
  `finance_transaction_tag_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_transaction_categories`
--

CREATE TABLE `finance_transaction_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_transaction_tags`
--

CREATE TABLE `finance_transaction_tags` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_00_000001_create_cache_table', 1),
(2, '0001_01_00_000002_create_jobs_table', 1),
(3, '0001_01_01_000001_create_users_table', 1),
(4, '0001_01_01_000002_1_create_document_versions_table', 1),
(5, '0001_01_01_000002_create_customers_table', 1),
(6, '0001_01_01_000002_create_finance_accounts_table', 1),
(7, '0001_01_01_000002_create_finance_transactions_table', 1),
(8, '0001_01_01_000002_create_suppliers_table', 1),
(9, '0001_01_01_000002_create_tax_schemes_table', 1),
(10, '0001_01_01_000002_create_user_activity_logs_table', 1),
(11, '0001_01_01_000003_01_create_customer_wallet_transactions_table', 1),
(12, '0001_01_01_000003_02_create_customer_wallet_trx_confirmations_table', 1),
(13, '0001_01_01_000003_create_product_categories_table', 1),
(14, '0001_01_01_000003_create_products_table', 1),
(15, '0001_01_01_000004_00_create_stock_movements_table', 1),
(16, '0001_01_01_000004_01_create_stock_adjustments_table', 1),
(17, '0001_01_01_000004_02_create_stock_adjustment_details_table', 1),
(18, '0001_01_01_000005_01_create_cashier_terminals_table', 1),
(19, '0001_01_01_000005_02_create_cashier_sessions_table', 1),
(20, '0001_01_01_000005_03_create_cashier_session_transactions_table', 1),
(21, '0001_01_01_000006_create_operational_costs_category_table', 1),
(22, '0001_01_01_000007_create_operational_costs_table', 1),
(23, '0001_01_01_000008_create_settings_table', 1),
(24, '0001_01_01_000009_create_purchase_orders_table', 1),
(25, '0001_01_01_000010_create_purchase_order_returns_table', 1),
(26, '0001_01_01_000011_create_purchase_order_details_table', 1),
(27, '0001_01_01_000012_create_purchase_order_payments_table', 1),
(28, '0001_01_01_000013_create_sales_orders_table', 1),
(29, '0001_01_01_000014_create_sales_order_returns_table', 1),
(30, '0001_01_01_000015_create_sales_order_details_table', 1),
(31, '0001_01_01_000016_create_sales_order_payments_table', 1),
(32, '2025_07_24_111206_create_permission_tables', 1),
(33, '2025_07_25_150050_create_personal_access_tokens_table', 1),
(34, '2025_11_06_161358_create_uoms_table', 1),
(35, '2025_11_12_171857_create_finance_transaction_indexes_1', 1),
(36, '2025_11_12_172939_create_finance_transaction_indexes_2', 1),
(37, '2025_11_13_072617_create_finance_transaction_categories_table', 1),
(38, '2025_11_14_072703_create_finance_transaction_tags_table', 1),
(39, '2025_11_14_072919_create_finance_transactions_has_tags_table', 1),
(40, '2025_11_19_203036_create_product_units_table', 1),
(41, '2025_11_19_203700_create_product_quanitity_prices_table', 1),
(42, '2025_11_21_092629_create_supplier_wallet_transactions_table', 1),
(43, '2025_11_21_093024_create_product_images_table', 1),
(44, '2025_12_01_094351_add_wallet_balance_to_supplliers_table', 1),
(45, '2025_12_01_125934_cashier_cash_drops', 1),
(46, '2025_12_01_192738_add_show_in_cashier_cash_drop_to_finance_accounts_table', 1),
(47, '2025_12_02_082403_add_discount_columns_to_sales_order_details', 1),
(48, '2025_12_02_082452_add_discount_columns_to_purchase_order_details', 1),
(49, '2025_12_02_094902_add_extra_columns_to_stock_movements_table', 1),
(50, '2025_12_02_165331_change_document_date_to_datetime_in_stock_movements', 1),
(51, '2025_12_03_141341_create_customer_ledgers_table', 1),
(52, '2025_12_03_141429_create_supplier_ledgers_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `operational_costs`
--

CREATE TABLE `operational_costs` (
  `id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `finance_account_id` bigint UNSIGNED DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `amount` decimal(8,0) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `operational_cost_categories`
--

CREATE TABLE `operational_cost_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `supplier_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `barcode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `price_editable` tinyint(1) NOT NULL DEFAULT '0',
  `cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price_1` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price_2` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price_3` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price_1_markup` decimal(5,2) NOT NULL DEFAULT '0.00',
  `price_2_markup` decimal(5,2) NOT NULL DEFAULT '0.00',
  `price_3_markup` decimal(5,2) NOT NULL DEFAULT '0.00',
  `price_1_option` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'price',
  `price_2_option` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'price',
  `price_3_option` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'price',
  `expiry_date` date DEFAULT NULL,
  `uom` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `stock` decimal(10,3) NOT NULL DEFAULT '0.000',
  `min_stock` decimal(10,3) NOT NULL DEFAULT '0.000',
  `max_stock` decimal(10,3) NOT NULL DEFAULT '0.000',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caption` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` smallint UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_quantity_prices`
--

CREATE TABLE `product_quantity_prices` (
  `id` bigint UNSIGNED NOT NULL,
  `product_unit_id` bigint UNSIGNED NOT NULL,
  `price_type` enum('price_1','price_2','price_3') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipe harga yang dimodifikasi.',
  `min_quantity` decimal(10,3) NOT NULL DEFAULT '1.000' COMMENT 'Kuantitas minimum untuk rentang harga ini.',
  `price` decimal(10,2) NOT NULL COMMENT 'Harga satuan yang berlaku.',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_units`
--

CREATE TABLE `product_units` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `barcode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Barcode unik untuk unit ini.',
  `conversion_factor` decimal(10,4) NOT NULL DEFAULT '1.0000' COMMENT 'Faktor konversi relatif terhadap unit dasar (base unit).',
  `is_base_unit` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'True jika ini adalah unit dasar produk.',
  `cost` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Harga Pokok Penjualan (HPP) per unit.',
  `price_1` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Harga Jual 1 (Eceran) default.',
  `price_2` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Harga Jual 2 (Partai) default.',
  `price_3` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Harga Jual 3 (Grosir) default.',
  `price_1_markup` decimal(10,2) DEFAULT NULL,
  `price_2_markup` decimal(10,2) DEFAULT NULL,
  `price_3_markup` decimal(10,2) DEFAULT NULL,
  `price_1_option` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_2_option` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_3_option` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` bigint UNSIGNED NOT NULL,
  `supplier_id` bigint UNSIGNED DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `supplier_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `supplier_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `supplier_phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `supplier_address` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `delivery_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datetime` datetime NOT NULL,
  `due_date` date DEFAULT NULL,
  `total` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_discount` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_tax` decimal(18,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_return` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_paid` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_refund` decimal(18,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(18,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_details`
--

CREATE TABLE `purchase_order_details` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `return_id` bigint UNSIGNED DEFAULT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `product_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_uom` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `quantity` decimal(18,3) NOT NULL DEFAULT '0.000',
  `user_input_qty` decimal(10,3) DEFAULT NULL,
  `user_input_uom` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conversion_rate` decimal(10,3) DEFAULT NULL,
  `cost` decimal(18,2) NOT NULL DEFAULT '0.00',
  `subtotal_cost` decimal(18,2) NOT NULL DEFAULT '0.00',
  `discount_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `subtotal_discount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `notes` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_payments`
--

CREATE TABLE `purchase_order_payments` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `return_id` bigint UNSIGNED DEFAULT NULL,
  `supplier_id` bigint UNSIGNED DEFAULT NULL,
  `finance_account_id` bigint UNSIGNED DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(18,2) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_returns`
--

CREATE TABLE `purchase_order_returns` (
  `id` bigint UNSIGNED NOT NULL,
  `purchase_order_id` bigint UNSIGNED DEFAULT NULL,
  `cashier_id` bigint UNSIGNED DEFAULT NULL,
  `supplier_id` bigint UNSIGNED DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `supplier_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `supplier_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `supplier_phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `supplier_address` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `refund_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datetime` datetime NOT NULL,
  `total_cost` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_discount` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_tax` decimal(18,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_refunded` decimal(18,2) NOT NULL DEFAULT '0.00',
  `remaining_refund` decimal(18,2) NOT NULL DEFAULT '0.00',
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_orders`
--

CREATE TABLE `sales_orders` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED DEFAULT NULL,
  `cashier_id` bigint UNSIGNED DEFAULT NULL,
  `cashier_session_id` bigint UNSIGNED DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `customer_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `customer_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `customer_phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `customer_address` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `delivery_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datetime` datetime NOT NULL,
  `due_date` date DEFAULT NULL,
  `total_cost` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_price` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_discount` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_tax` decimal(18,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_return` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_paid` decimal(18,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(18,2) NOT NULL DEFAULT '0.00',
  `change` decimal(18,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_order_details`
--

CREATE TABLE `sales_order_details` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `return_id` bigint UNSIGNED DEFAULT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `product_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `product_barcode` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `product_description` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `product_uom` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `quantity` decimal(18,3) NOT NULL DEFAULT '0.000',
  `user_input_qty` decimal(10,3) DEFAULT NULL,
  `user_input_uom` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conversion_rate` decimal(10,3) DEFAULT NULL,
  `cost` decimal(18,2) NOT NULL DEFAULT '0.00',
  `price` decimal(18,2) NOT NULL DEFAULT '0.00',
  `discount_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `subtotal_discount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `subtotal_cost` decimal(18,2) NOT NULL DEFAULT '0.00',
  `subtotal_price` decimal(18,2) NOT NULL DEFAULT '0.00',
  `notes` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_order_payments`
--

CREATE TABLE `sales_order_payments` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `return_id` bigint UNSIGNED DEFAULT NULL,
  `customer_id` bigint UNSIGNED DEFAULT NULL,
  `finance_account_id` bigint UNSIGNED DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(18,2) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_order_returns`
--

CREATE TABLE `sales_order_returns` (
  `id` bigint UNSIGNED NOT NULL,
  `sales_order_id` bigint UNSIGNED DEFAULT NULL,
  `cashier_id` bigint UNSIGNED DEFAULT NULL,
  `customer_id` bigint UNSIGNED DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `customer_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `customer_phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `customer_address` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `refund_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datetime` datetime NOT NULL,
  `total_cost` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_price` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_discount` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_tax` decimal(18,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_refunded` decimal(18,2) NOT NULL DEFAULT '0.00',
  `remaining_refund` decimal(18,2) NOT NULL DEFAULT '0.00',
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_adjustments`
--

CREATE TABLE `stock_adjustments` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datetime` datetime DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_adjustment_details`
--

CREATE TABLE `stock_adjustment_details` (
  `id` bigint UNSIGNED NOT NULL,
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `old_quantity` decimal(10,3) NOT NULL DEFAULT '0.000',
  `new_quantity` decimal(10,3) NOT NULL DEFAULT '0.000',
  `balance` decimal(10,3) NOT NULL DEFAULT '0.000',
  `user_input_qty` decimal(10,3) DEFAULT NULL,
  `user_input_uom` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conversion_rate` decimal(10,3) DEFAULT NULL,
  `uom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `subtotal_cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `subtotal_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `ref_id` bigint UNSIGNED DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ref_type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `parent_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID dokumen induk, misalnya SO/PO/SA',
  `parent_ref_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tipe dokumen induk: sales_order, purchase_order, stock_adjustment, dll',
  `quantity` decimal(10,3) NOT NULL DEFAULT '0.000',
  `user_input_qty` decimal(10,3) DEFAULT NULL,
  `user_input_uom` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conversion_rate` decimal(10,3) DEFAULT NULL,
  `product_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `uom` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `quantity_before` decimal(10,3) NOT NULL DEFAULT '0.000',
  `quantity_after` decimal(10,3) NOT NULL DEFAULT '0.000',
  `notes` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `party_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID customer atau supplier',
  `party_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Jenis party: customer / supplier',
  `party_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `party_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nama customer/supplier (snapshot)',
  `document_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nomor dokumen induk (misalnya SO-2025-0012)',
  `document_datetime` datetime DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_1` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `phone_2` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `phone_3` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `bank_account_name_1` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `bank_account_number_1` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `bank_account_holder_1` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `bank_account_name_2` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `bank_account_number_2` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `bank_account_holder_2` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `return_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `balance` decimal(15,0) NOT NULL DEFAULT '0',
  `wallet_balance` decimal(15,0) NOT NULL DEFAULT '0',
  `url_1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `url_2` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_ledgers`
--

CREATE TABLE `supplier_ledgers` (
  `id` bigint UNSIGNED NOT NULL,
  `supplier_id` bigint UNSIGNED NOT NULL,
  `finance_account_id` bigint UNSIGNED DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_id` bigint UNSIGNED DEFAULT NULL,
  `datetime` datetime NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `running_balance` decimal(12,2) DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_wallet_transactions`
--

CREATE TABLE `supplier_wallet_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `finance_account_id` bigint UNSIGNED DEFAULT NULL,
  `supplier_id` bigint UNSIGNED DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ref_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_id` bigint UNSIGNED DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tax_schemes`
--

CREATE TABLE `tax_schemes` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nama skema pajak (misal: PPN 11%)',
  `rate_percentage` decimal(5,2) NOT NULL COMMENT 'Tarif pajak (misal: 11.00)',
  `is_inclusive` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'TRUE jika harga sudah termasuk pajak (Inclusive)',
  `tax_authority` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Otoritas penerima pajak',
  `description` text COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `uoms`
--

CREATE TABLE `uoms` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `last_login_datetime` datetime DEFAULT NULL,
  `last_activity_description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `last_activity_datetime` datetime DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `name`, `password`, `type`, `active`, `last_login_datetime`, `last_activity_description`, `last_activity_datetime`, `remember_token`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`, `deleted_by`) VALUES
(1, 'admin', 'Administrator', '$2y$12$nyZ.fYlKmogGVC.K1i/8gOk61Fi8.lJBddiSiH9.uZ17dwL66KKb2', 'super_user', 1, NULL, '', NULL, 'DsjWOGYpc2', '2025-12-20 11:17:40', NULL, NULL, NULL, NULL, NULL),
(2, 'kasir1', 'Kasir 1', '$2y$12$nyZ.fYlKmogGVC.K1i/8gOk61Fi8.lJBddiSiH9.uZ17dwL66KKb2', 'standard_user', 1, NULL, '', NULL, 'MBj4LFT9Md', '2025-12-20 11:17:40', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_logs`
--

CREATE TABLE `user_activity_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logged_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activity_category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activity_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acl_model_has_permissions`
--
ALTER TABLE `acl_model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `acl_model_has_roles`
--
ALTER TABLE `acl_model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `acl_permissions`
--
ALTER TABLE `acl_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `acl_permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `acl_roles`
--
ALTER TABLE `acl_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `acl_roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `acl_role_has_permissions`
--
ALTER TABLE `acl_role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `acl_role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cashier_cash_drops`
--
ALTER TABLE `cashier_cash_drops`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cashier_cash_drops_code_unique` (`code`),
  ADD KEY `cashier_cash_drops_cashier_id_foreign` (`cashier_id`),
  ADD KEY `cashier_cash_drops_terminal_id_foreign` (`terminal_id`),
  ADD KEY `cashier_cash_drops_cashier_session_id_foreign` (`cashier_session_id`),
  ADD KEY `cashier_cash_drops_source_finance_account_id_foreign` (`source_finance_account_id`),
  ADD KEY `cashier_cash_drops_target_finance_account_id_foreign` (`target_finance_account_id`),
  ADD KEY `cashier_cash_drops_approved_by_foreign` (`approved_by`),
  ADD KEY `cashier_cash_drops_created_by_foreign` (`created_by`),
  ADD KEY `cashier_cash_drops_updated_by_foreign` (`updated_by`),
  ADD KEY `cashier_cash_drops_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `cashier_sessions`
--
ALTER TABLE `cashier_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cashier_sessions_created_by_foreign` (`created_by`),
  ADD KEY `cashier_sessions_deleted_by_foreign` (`deleted_by`),
  ADD KEY `cashier_sessions_user_id_index` (`user_id`),
  ADD KEY `cashier_sessions_cashier_terminal_id_index` (`cashier_terminal_id`),
  ADD KEY `cashier_sessions_is_closed_index` (`is_closed`),
  ADD KEY `cashier_sessions_opened_at_index` (`opened_at`),
  ADD KEY `cashier_sessions_closed_at_index` (`closed_at`);

--
-- Indexes for table `cashier_session_transactions`
--
ALTER TABLE `cashier_session_transactions`
  ADD PRIMARY KEY (`cashier_session_id`,`finance_transaction_id`),
  ADD KEY `cashier_session_transactions_finance_transaction_id_foreign` (`finance_transaction_id`);

--
-- Indexes for table `cashier_terminals`
--
ALTER TABLE `cashier_terminals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cashier_terminals_finance_account_id_unique` (`finance_account_id`),
  ADD UNIQUE KEY `cashier_terminals_name_unique` (`name`),
  ADD KEY `cashier_terminals_created_by_foreign` (`created_by`),
  ADD KEY `cashier_terminals_deleted_by_foreign` (`deleted_by`),
  ADD KEY `cashier_terminals_active_index` (`active`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customers_code_unique` (`code`),
  ADD KEY `customers_created_by_foreign` (`created_by`),
  ADD KEY `customers_updated_by_foreign` (`updated_by`),
  ADD KEY `customers_deleted_by_foreign` (`deleted_by`),
  ADD KEY `customers_type_index` (`type`),
  ADD KEY `customers_name_index` (`name`),
  ADD KEY `customers_email_index` (`email`),
  ADD KEY `customers_phone_index` (`phone`),
  ADD KEY `customers_wallet_balance_index` (`wallet_balance`),
  ADD KEY `customers_balance_index` (`balance`),
  ADD KEY `customers_active_index` (`active`),
  ADD KEY `customers_last_login_datetime_index` (`last_login_datetime`);

--
-- Indexes for table `customer_ledgers`
--
ALTER TABLE `customer_ledgers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_ledgers_code_unique` (`code`),
  ADD KEY `customer_ledgers_customer_id_foreign` (`customer_id`),
  ADD KEY `customer_ledgers_finance_account_id_foreign` (`finance_account_id`),
  ADD KEY `customer_ledgers_ref_type_ref_id_index` (`ref_type`,`ref_id`),
  ADD KEY `customer_ledgers_created_by_foreign` (`created_by`),
  ADD KEY `customer_ledgers_updated_by_foreign` (`updated_by`),
  ADD KEY `customer_ledgers_deleted_by_foreign` (`deleted_by`),
  ADD KEY `customer_ledgers_datetime_index` (`datetime`),
  ADD KEY `customer_ledgers_type_index` (`type`);

--
-- Indexes for table `customer_password_reset_tokens`
--
ALTER TABLE `customer_password_reset_tokens`
  ADD PRIMARY KEY (`code`);

--
-- Indexes for table `customer_sessions`
--
ALTER TABLE `customer_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_sessions_customer_account_id_index` (`customer_account_id`),
  ADD KEY `customer_sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `customer_wallet_transactions`
--
ALTER TABLE `customer_wallet_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_wallet_transactions_code_unique` (`code`),
  ADD KEY `customer_wallet_transactions_finance_account_id_foreign` (`finance_account_id`),
  ADD KEY `customer_wallet_transactions_customer_id_foreign` (`customer_id`),
  ADD KEY `customer_wallet_transactions_ref_type_ref_id_index` (`ref_type`,`ref_id`),
  ADD KEY `customer_wallet_transactions_created_by_foreign` (`created_by`),
  ADD KEY `customer_wallet_transactions_updated_by_foreign` (`updated_by`),
  ADD KEY `customer_wallet_transactions_deleted_by_foreign` (`deleted_by`),
  ADD KEY `customer_wallet_transactions_datetime_index` (`datetime`),
  ADD KEY `customer_wallet_transactions_type_index` (`type`);

--
-- Indexes for table `customer_wallet_trx_confirmations`
--
ALTER TABLE `customer_wallet_trx_confirmations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_wallet_trx_confirmations_code_unique` (`code`),
  ADD KEY `customer_wallet_trx_confirmations_finance_account_id_foreign` (`finance_account_id`),
  ADD KEY `customer_wallet_trx_confirmations_customer_id_foreign` (`customer_id`),
  ADD KEY `customer_wallet_trx_confirmations_created_by_foreign` (`created_by`),
  ADD KEY `customer_wallet_trx_confirmations_updated_by_foreign` (`updated_by`),
  ADD KEY `customer_wallet_trx_confirmations_deleted_by_foreign` (`deleted_by`),
  ADD KEY `customer_wallet_trx_confirmations_datetime_index` (`datetime`),
  ADD KEY `customer_wallet_trx_confirmations_status_index` (`status`);

--
-- Indexes for table `document_versions`
--
ALTER TABLE `document_versions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `document_versions_document_type_document_id_version_unique` (`document_type`,`document_id`,`version`),
  ADD KEY `document_versions_document_type_document_id_index` (`document_type`,`document_id`),
  ADD KEY `document_versions_created_by_foreign` (`created_by`),
  ADD KEY `document_versions_is_deleted_index` (`is_deleted`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `finance_accounts`
--
ALTER TABLE `finance_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `finance_accounts_name_unique` (`name`),
  ADD KEY `finance_accounts_created_by_foreign` (`created_by`),
  ADD KEY `finance_accounts_updated_by_foreign` (`updated_by`),
  ADD KEY `finance_accounts_deleted_by_foreign` (`deleted_by`),
  ADD KEY `finance_accounts_type_index` (`type`),
  ADD KEY `finance_accounts_balance_index` (`balance`),
  ADD KEY `finance_accounts_active_index` (`active`),
  ADD KEY `finance_accounts_show_in_pos_payment_index` (`show_in_pos_payment`),
  ADD KEY `finance_accounts_show_in_purchasing_payment_index` (`show_in_purchasing_payment`),
  ADD KEY `finance_accounts_has_wallet_access_index` (`has_wallet_access`);

--
-- Indexes for table `finance_transactions`
--
ALTER TABLE `finance_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `finance_transactions_code_unique` (`code`),
  ADD KEY `finance_transactions_ref_type_ref_id_index` (`ref_type`,`ref_id`),
  ADD KEY `finance_transactions_created_by_foreign` (`created_by`),
  ADD KEY `finance_transactions_updated_by_foreign` (`updated_by`),
  ADD KEY `finance_transactions_deleted_by_foreign` (`deleted_by`),
  ADD KEY `finance_transactions_datetime_index` (`datetime`),
  ADD KEY `finance_transactions_type_index` (`type`),
  ADD KEY `finance_transactions_created_at_account_id_index` (`created_at`,`account_id`),
  ADD KEY `finance_transactions_datetime_type_account_id_deleted_at_index` (`datetime`,`type`,`account_id`,`deleted_at`),
  ADD KEY `finance_transactions_account_id_datetime_index` (`account_id`,`datetime`),
  ADD KEY `finance_transactions_category_id_foreign` (`category_id`);

--
-- Indexes for table `finance_transactions_has_tags`
--
ALTER TABLE `finance_transactions_has_tags`
  ADD UNIQUE KEY `ft_tag_unique` (`finance_transaction_id`,`finance_transaction_tag_id`),
  ADD KEY `finance_transactions_has_tags_finance_transaction_id_index` (`finance_transaction_id`),
  ADD KEY `finance_transactions_has_tags_finance_transaction_tag_id_index` (`finance_transaction_tag_id`);

--
-- Indexes for table `finance_transaction_categories`
--
ALTER TABLE `finance_transaction_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `finance_transaction_categories_name_unique` (`name`),
  ADD KEY `finance_transaction_categories_created_by_foreign` (`created_by`),
  ADD KEY `finance_transaction_categories_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `finance_transaction_tags`
--
ALTER TABLE `finance_transaction_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `finance_transaction_tags_name_unique` (`name`),
  ADD KEY `finance_transaction_tags_created_by_foreign` (`created_by`),
  ADD KEY `finance_transaction_tags_updated_by_foreign` (`updated_by`),
  ADD KEY `finance_transaction_tags_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `operational_costs`
--
ALTER TABLE `operational_costs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `operational_costs_code_unique` (`code`),
  ADD KEY `operational_costs_category_id_foreign` (`category_id`),
  ADD KEY `operational_costs_finance_account_id_foreign` (`finance_account_id`),
  ADD KEY `operational_costs_created_by_foreign` (`created_by`),
  ADD KEY `operational_costs_deleted_by_foreign` (`deleted_by`),
  ADD KEY `operational_costs_date_index` (`date`),
  ADD KEY `operational_costs_description_index` (`description`);

--
-- Indexes for table `operational_cost_categories`
--
ALTER TABLE `operational_cost_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `operational_cost_categories_name_unique` (`name`),
  ADD KEY `operational_cost_categories_created_by_foreign` (`created_by`),
  ADD KEY `operational_cost_categories_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_name_unique` (`name`),
  ADD KEY `products_category_id_foreign` (`category_id`),
  ADD KEY `products_supplier_id_foreign` (`supplier_id`),
  ADD KEY `products_created_by_foreign` (`created_by`),
  ADD KEY `products_updated_by_foreign` (`updated_by`),
  ADD KEY `products_deleted_by_foreign` (`deleted_by`),
  ADD KEY `products_barcode_index` (`barcode`),
  ADD KEY `products_type_index` (`type`),
  ADD KEY `products_active_index` (`active`),
  ADD KEY `products_stock_index` (`stock`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_categories_name_unique` (`name`),
  ADD KEY `product_categories_created_by_foreign` (`created_by`),
  ADD KEY `product_categories_updated_by_foreign` (`updated_by`),
  ADD KEY `product_categories_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_featured_unique` (`product_id`,`is_featured`),
  ADD KEY `product_images_created_by_foreign` (`created_by`),
  ADD KEY `product_images_updated_by_foreign` (`updated_by`),
  ADD KEY `product_images_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `product_quantity_prices`
--
ALTER TABLE `product_quantity_prices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unit_price_qty_unique` (`product_unit_id`,`price_type`,`min_quantity`);

--
-- Indexes for table `product_units`
--
ALTER TABLE `product_units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_units_product_id_name_unique` (`product_id`,`name`),
  ADD UNIQUE KEY `product_units_barcode_unique` (`barcode`),
  ADD KEY `product_units_created_by_foreign` (`created_by`),
  ADD KEY `product_units_updated_by_foreign` (`updated_by`),
  ADD KEY `product_units_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_orders_code_unique` (`code`),
  ADD KEY `1` (`supplier_id`),
  ADD KEY `purchase_orders_created_by_foreign` (`created_by`),
  ADD KEY `purchase_orders_deleted_by_foreign` (`deleted_by`),
  ADD KEY `purchase_orders_type_index` (`type`),
  ADD KEY `purchase_orders_status_index` (`status`),
  ADD KEY `purchase_orders_payment_status_index` (`payment_status`),
  ADD KEY `purchase_orders_delivery_status_index` (`delivery_status`),
  ADD KEY `purchase_orders_datetime_index` (`datetime`),
  ADD KEY `purchase_orders_due_date_index` (`due_date`);

--
-- Indexes for table `purchase_order_details`
--
ALTER TABLE `purchase_order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_order_details_order_id_foreign` (`order_id`),
  ADD KEY `purchase_order_details_return_id_foreign` (`return_id`),
  ADD KEY `purchase_order_details_product_id_foreign` (`product_id`),
  ADD KEY `purchase_order_details_created_by_foreign` (`created_by`),
  ADD KEY `purchase_order_details_updated_by_foreign` (`updated_by`),
  ADD KEY `purchase_order_details_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `purchase_order_payments`
--
ALTER TABLE `purchase_order_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_order_payments_code_unique` (`code`),
  ADD KEY `purchase_order_payments_order_id_foreign` (`order_id`),
  ADD KEY `purchase_order_payments_return_id_foreign` (`return_id`),
  ADD KEY `purchase_order_payments_supplier_id_foreign` (`supplier_id`),
  ADD KEY `purchase_order_payments_finance_account_id_foreign` (`finance_account_id`),
  ADD KEY `purchase_order_payments_created_by_foreign` (`created_by`),
  ADD KEY `purchase_order_payments_deleted_by_foreign` (`deleted_by`),
  ADD KEY `purchase_order_payments_type_index` (`type`),
  ADD KEY `purchase_order_payments_amount_index` (`amount`);

--
-- Indexes for table `purchase_order_returns`
--
ALTER TABLE `purchase_order_returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_order_returns_code_unique` (`code`),
  ADD KEY `purchase_order_returns_purchase_order_id_foreign` (`purchase_order_id`),
  ADD KEY `purchase_order_returns_cashier_id_foreign` (`cashier_id`),
  ADD KEY `purchase_order_returns_supplier_id_foreign` (`supplier_id`),
  ADD KEY `purchase_order_returns_created_by_foreign` (`created_by`),
  ADD KEY `purchase_order_returns_updated_by_foreign` (`updated_by`),
  ADD KEY `purchase_order_returns_deleted_by_foreign` (`deleted_by`),
  ADD KEY `purchase_order_returns_status_index` (`status`),
  ADD KEY `purchase_order_returns_refund_status_index` (`refund_status`),
  ADD KEY `purchase_order_returns_datetime_index` (`datetime`),
  ADD KEY `purchase_order_returns_remaining_refund_index` (`remaining_refund`);

--
-- Indexes for table `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sales_orders_code_unique` (`code`),
  ADD KEY `sales_orders_customer_id_foreign` (`customer_id`),
  ADD KEY `sales_orders_cashier_id_foreign` (`cashier_id`),
  ADD KEY `sales_orders_cashier_session_id_foreign` (`cashier_session_id`),
  ADD KEY `sales_orders_created_by_foreign` (`created_by`),
  ADD KEY `sales_orders_updated_by_foreign` (`updated_by`),
  ADD KEY `sales_orders_deleted_by_foreign` (`deleted_by`),
  ADD KEY `sales_orders_type_index` (`type`),
  ADD KEY `sales_orders_status_index` (`status`),
  ADD KEY `sales_orders_payment_status_index` (`payment_status`),
  ADD KEY `sales_orders_delivery_status_index` (`delivery_status`),
  ADD KEY `sales_orders_datetime_index` (`datetime`),
  ADD KEY `sales_orders_due_date_index` (`due_date`);

--
-- Indexes for table `sales_order_details`
--
ALTER TABLE `sales_order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_order_details_order_id_foreign` (`order_id`),
  ADD KEY `sales_order_details_return_id_foreign` (`return_id`),
  ADD KEY `sales_order_details_product_id_foreign` (`product_id`),
  ADD KEY `sales_order_details_created_by_foreign` (`created_by`),
  ADD KEY `sales_order_details_updated_by_foreign` (`updated_by`),
  ADD KEY `sales_order_details_deleted_by_foreign` (`deleted_by`),
  ADD KEY `sales_order_details_product_barcode_index` (`product_barcode`);

--
-- Indexes for table `sales_order_payments`
--
ALTER TABLE `sales_order_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sales_order_payments_code_unique` (`code`),
  ADD KEY `sales_order_payments_order_id_foreign` (`order_id`),
  ADD KEY `sales_order_payments_return_id_foreign` (`return_id`),
  ADD KEY `sales_order_payments_customer_id_foreign` (`customer_id`),
  ADD KEY `sales_order_payments_finance_account_id_foreign` (`finance_account_id`),
  ADD KEY `sales_order_payments_created_by_foreign` (`created_by`),
  ADD KEY `sales_order_payments_deleted_by_foreign` (`deleted_by`),
  ADD KEY `sales_order_payments_type_index` (`type`);

--
-- Indexes for table `sales_order_returns`
--
ALTER TABLE `sales_order_returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sales_order_returns_code_unique` (`code`),
  ADD KEY `sales_order_returns_sales_order_id_foreign` (`sales_order_id`),
  ADD KEY `sales_order_returns_cashier_id_foreign` (`cashier_id`),
  ADD KEY `sales_order_returns_customer_id_foreign` (`customer_id`),
  ADD KEY `sales_order_returns_created_by_foreign` (`created_by`),
  ADD KEY `sales_order_returns_updated_by_foreign` (`updated_by`),
  ADD KEY `sales_order_returns_deleted_by_foreign` (`deleted_by`),
  ADD KEY `sales_order_returns_status_index` (`status`),
  ADD KEY `sales_order_returns_refund_status_index` (`refund_status`),
  ADD KEY `sales_order_returns_datetime_index` (`datetime`),
  ADD KEY `sales_order_returns_remaining_refund_index` (`remaining_refund`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`key`),
  ADD KEY `settings_created_by_foreign` (`created_by`),
  ADD KEY `settings_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stock_adjustments_code_unique` (`code`),
  ADD KEY `stock_adjustments_created_by_foreign` (`created_by`),
  ADD KEY `stock_adjustments_updated_by_foreign` (`updated_by`),
  ADD KEY `stock_adjustments_deleted_by_foreign` (`deleted_by`),
  ADD KEY `stock_adjustments_datetime_index` (`datetime`),
  ADD KEY `stock_adjustments_status_index` (`status`),
  ADD KEY `stock_adjustments_type_index` (`type`);

--
-- Indexes for table `stock_adjustment_details`
--
ALTER TABLE `stock_adjustment_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_adjustment_details_parent_id_foreign` (`parent_id`),
  ADD KEY `stock_adjustment_details_product_id_foreign` (`product_id`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stock_movements_code_unique` (`code`),
  ADD KEY `stock_movements_product_id_foreign` (`product_id`),
  ADD KEY `stock_movements_ref_type_ref_id_index` (`ref_type`,`ref_id`),
  ADD KEY `stock_movements_created_by_foreign` (`created_by`),
  ADD KEY `stock_movements_deleted_by_foreign` (`deleted_by`),
  ADD KEY `stock_movements_parent_id_index` (`parent_id`),
  ADD KEY `stock_movements_parent_ref_type_index` (`parent_ref_type`),
  ADD KEY `stock_movements_party_id_index` (`party_id`),
  ADD KEY `stock_movements_party_type_index` (`party_type`),
  ADD KEY `stock_movements_document_code_index` (`document_code`),
  ADD KEY `stock_movements_party_code_index` (`party_code`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `suppliers_code_unique` (`code`),
  ADD KEY `suppliers_created_by_foreign` (`created_by`),
  ADD KEY `suppliers_updated_by_foreign` (`updated_by`),
  ADD KEY `suppliers_deleted_by_foreign` (`deleted_by`),
  ADD KEY `suppliers_name_index` (`name`),
  ADD KEY `suppliers_phone_1_index` (`phone_1`),
  ADD KEY `suppliers_phone_2_index` (`phone_2`),
  ADD KEY `suppliers_phone_3_index` (`phone_3`),
  ADD KEY `suppliers_active_index` (`active`),
  ADD KEY `suppliers_balance_index` (`balance`),
  ADD KEY `suppliers_wallet_balance_index` (`wallet_balance`);

--
-- Indexes for table `supplier_ledgers`
--
ALTER TABLE `supplier_ledgers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `supplier_ledgers_code_unique` (`code`),
  ADD KEY `supplier_ledgers_supplier_id_foreign` (`supplier_id`),
  ADD KEY `supplier_ledgers_finance_account_id_foreign` (`finance_account_id`),
  ADD KEY `supplier_ledgers_ref_type_ref_id_index` (`ref_type`,`ref_id`),
  ADD KEY `supplier_ledgers_created_by_foreign` (`created_by`),
  ADD KEY `supplier_ledgers_updated_by_foreign` (`updated_by`),
  ADD KEY `supplier_ledgers_deleted_by_foreign` (`deleted_by`),
  ADD KEY `supplier_ledgers_datetime_index` (`datetime`),
  ADD KEY `supplier_ledgers_type_index` (`type`);

--
-- Indexes for table `supplier_wallet_transactions`
--
ALTER TABLE `supplier_wallet_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `supplier_wallet_transactions_code_unique` (`code`),
  ADD KEY `supplier_wallet_transactions_finance_account_id_foreign` (`finance_account_id`),
  ADD KEY `supplier_wallet_transactions_supplier_id_foreign` (`supplier_id`),
  ADD KEY `supplier_wallet_transactions_ref_type_ref_id_index` (`ref_type`,`ref_id`),
  ADD KEY `supplier_wallet_transactions_created_by_foreign` (`created_by`),
  ADD KEY `supplier_wallet_transactions_updated_by_foreign` (`updated_by`),
  ADD KEY `supplier_wallet_transactions_deleted_by_foreign` (`deleted_by`),
  ADD KEY `supplier_wallet_transactions_datetime_index` (`datetime`),
  ADD KEY `supplier_wallet_transactions_type_index` (`type`);

--
-- Indexes for table `tax_schemes`
--
ALTER TABLE `tax_schemes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tax_schemes_name_unique` (`name`),
  ADD KEY `tax_schemes_created_by_foreign` (`created_by`),
  ADD KEY `tax_schemes_updated_by_foreign` (`updated_by`),
  ADD KEY `tax_schemes_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `uoms`
--
ALTER TABLE `uoms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uoms_name_unique` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD KEY `users_name_index` (`name`),
  ADD KEY `users_type_index` (`type`),
  ADD KEY `users_active_index` (`active`),
  ADD KEY `users_last_login_datetime_index` (`last_login_datetime`),
  ADD KEY `users_last_activity_description_index` (`last_activity_description`),
  ADD KEY `users_last_activity_datetime_index` (`last_activity_datetime`),
  ADD KEY `users_created_by_foreign` (`created_by`),
  ADD KEY `users_updated_by_foreign` (`updated_by`),
  ADD KEY `users_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_activity_logs_user_id_index` (`user_id`),
  ADD KEY `user_activity_logs_username_index` (`username`),
  ADD KEY `user_activity_logs_logged_at_index` (`logged_at`),
  ADD KEY `user_activity_logs_activity_category_index` (`activity_category`),
  ADD KEY `user_activity_logs_activity_name_index` (`activity_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `acl_permissions`
--
ALTER TABLE `acl_permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=134;

--
-- AUTO_INCREMENT for table `acl_roles`
--
ALTER TABLE `acl_roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cashier_cash_drops`
--
ALTER TABLE `cashier_cash_drops`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cashier_sessions`
--
ALTER TABLE `cashier_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cashier_terminals`
--
ALTER TABLE `cashier_terminals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_ledgers`
--
ALTER TABLE `customer_ledgers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_wallet_transactions`
--
ALTER TABLE `customer_wallet_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_wallet_trx_confirmations`
--
ALTER TABLE `customer_wallet_trx_confirmations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_versions`
--
ALTER TABLE `document_versions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_accounts`
--
ALTER TABLE `finance_accounts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `finance_transactions`
--
ALTER TABLE `finance_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_transaction_categories`
--
ALTER TABLE `finance_transaction_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finance_transaction_tags`
--
ALTER TABLE `finance_transaction_tags`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `operational_costs`
--
ALTER TABLE `operational_costs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `operational_cost_categories`
--
ALTER TABLE `operational_cost_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_quantity_prices`
--
ALTER TABLE `product_quantity_prices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_units`
--
ALTER TABLE `product_units`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_order_details`
--
ALTER TABLE `purchase_order_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_order_payments`
--
ALTER TABLE `purchase_order_payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_order_returns`
--
ALTER TABLE `purchase_order_returns`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_orders`
--
ALTER TABLE `sales_orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_order_details`
--
ALTER TABLE `sales_order_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_order_payments`
--
ALTER TABLE `sales_order_payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_order_returns`
--
ALTER TABLE `sales_order_returns`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_adjustment_details`
--
ALTER TABLE `stock_adjustment_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_ledgers`
--
ALTER TABLE `supplier_ledgers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_wallet_transactions`
--
ALTER TABLE `supplier_wallet_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tax_schemes`
--
ALTER TABLE `tax_schemes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `uoms`
--
ALTER TABLE `uoms`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `acl_model_has_permissions`
--
ALTER TABLE `acl_model_has_permissions`
  ADD CONSTRAINT `acl_model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `acl_permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `acl_model_has_roles`
--
ALTER TABLE `acl_model_has_roles`
  ADD CONSTRAINT `acl_model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `acl_roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `acl_role_has_permissions`
--
ALTER TABLE `acl_role_has_permissions`
  ADD CONSTRAINT `acl_role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `acl_permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `acl_role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `acl_roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cashier_cash_drops`
--
ALTER TABLE `cashier_cash_drops`
  ADD CONSTRAINT `cashier_cash_drops_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cashier_cash_drops_cashier_id_foreign` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cashier_cash_drops_cashier_session_id_foreign` FOREIGN KEY (`cashier_session_id`) REFERENCES `cashier_sessions` (`id`),
  ADD CONSTRAINT `cashier_cash_drops_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cashier_cash_drops_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cashier_cash_drops_source_finance_account_id_foreign` FOREIGN KEY (`source_finance_account_id`) REFERENCES `finance_accounts` (`id`),
  ADD CONSTRAINT `cashier_cash_drops_target_finance_account_id_foreign` FOREIGN KEY (`target_finance_account_id`) REFERENCES `finance_accounts` (`id`),
  ADD CONSTRAINT `cashier_cash_drops_terminal_id_foreign` FOREIGN KEY (`terminal_id`) REFERENCES `cashier_terminals` (`id`),
  ADD CONSTRAINT `cashier_cash_drops_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cashier_sessions`
--
ALTER TABLE `cashier_sessions`
  ADD CONSTRAINT `cashier_sessions_cashier_terminal_id_foreign` FOREIGN KEY (`cashier_terminal_id`) REFERENCES `cashier_terminals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cashier_sessions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cashier_sessions_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cashier_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cashier_session_transactions`
--
ALTER TABLE `cashier_session_transactions`
  ADD CONSTRAINT `cashier_session_transactions_cashier_session_id_foreign` FOREIGN KEY (`cashier_session_id`) REFERENCES `cashier_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cashier_session_transactions_finance_transaction_id_foreign` FOREIGN KEY (`finance_transaction_id`) REFERENCES `finance_transactions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cashier_terminals`
--
ALTER TABLE `cashier_terminals`
  ADD CONSTRAINT `cashier_terminals_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cashier_terminals_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cashier_terminals_finance_account_id_foreign` FOREIGN KEY (`finance_account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customers_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `customer_ledgers`
--
ALTER TABLE `customer_ledgers`
  ADD CONSTRAINT `customer_ledgers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_ledgers_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `customer_ledgers_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_ledgers_finance_account_id_foreign` FOREIGN KEY (`finance_account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `customer_ledgers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `customer_wallet_transactions`
--
ALTER TABLE `customer_wallet_transactions`
  ADD CONSTRAINT `customer_wallet_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_wallet_transactions_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `customer_wallet_transactions_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_wallet_transactions_finance_account_id_foreign` FOREIGN KEY (`finance_account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `customer_wallet_transactions_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `customer_wallet_trx_confirmations`
--
ALTER TABLE `customer_wallet_trx_confirmations`
  ADD CONSTRAINT `customer_wallet_trx_confirmations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_wallet_trx_confirmations_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `customer_wallet_trx_confirmations_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_wallet_trx_confirmations_finance_account_id_foreign` FOREIGN KEY (`finance_account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `customer_wallet_trx_confirmations_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `document_versions`
--
ALTER TABLE `document_versions`
  ADD CONSTRAINT `document_versions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `finance_accounts`
--
ALTER TABLE `finance_accounts`
  ADD CONSTRAINT `finance_accounts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `finance_accounts_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `finance_accounts_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `finance_transactions`
--
ALTER TABLE `finance_transactions`
  ADD CONSTRAINT `finance_transactions_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `finance_transactions_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `finance_transaction_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `finance_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `finance_transactions_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `finance_transactions_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `finance_transactions_has_tags`
--
ALTER TABLE `finance_transactions_has_tags`
  ADD CONSTRAINT `finance_transactions_has_tags_finance_transaction_id_foreign` FOREIGN KEY (`finance_transaction_id`) REFERENCES `finance_transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `finance_transactions_has_tags_finance_transaction_tag_id_foreign` FOREIGN KEY (`finance_transaction_tag_id`) REFERENCES `finance_transaction_tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `finance_transaction_categories`
--
ALTER TABLE `finance_transaction_categories`
  ADD CONSTRAINT `finance_transaction_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `finance_transaction_categories_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `finance_transaction_tags`
--
ALTER TABLE `finance_transaction_tags`
  ADD CONSTRAINT `finance_transaction_tags_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `finance_transaction_tags_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `finance_transaction_tags_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `operational_costs`
--
ALTER TABLE `operational_costs`
  ADD CONSTRAINT `operational_costs_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `operational_cost_categories` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `operational_costs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `operational_costs_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `operational_costs_finance_account_id_foreign` FOREIGN KEY (`finance_account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `operational_cost_categories`
--
ALTER TABLE `operational_cost_categories`
  ADD CONSTRAINT `operational_cost_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `operational_cost_categories_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `products_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `products_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD CONSTRAINT `product_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_categories_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_categories_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_images_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_images_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_quantity_prices`
--
ALTER TABLE `product_quantity_prices`
  ADD CONSTRAINT `product_quantity_prices_product_unit_id_foreign` FOREIGN KEY (`product_unit_id`) REFERENCES `product_units` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_units`
--
ALTER TABLE `product_units`
  ADD CONSTRAINT `product_units_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_units_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_units_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_units_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `purchase_orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_orders_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_order_details`
--
ALTER TABLE `purchase_order_details`
  ADD CONSTRAINT `purchase_order_details_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_order_details_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_order_details_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_order_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `purchase_order_details_return_id_foreign` FOREIGN KEY (`return_id`) REFERENCES `purchase_order_returns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_order_details_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_order_payments`
--
ALTER TABLE `purchase_order_payments`
  ADD CONSTRAINT `purchase_order_payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_order_payments_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_order_payments_finance_account_id_foreign` FOREIGN KEY (`finance_account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `purchase_order_payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_order_payments_return_id_foreign` FOREIGN KEY (`return_id`) REFERENCES `purchase_order_returns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_order_payments_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `purchase_order_returns`
--
ALTER TABLE `purchase_order_returns`
  ADD CONSTRAINT `purchase_order_returns_cashier_id_foreign` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `purchase_order_returns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_order_returns_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_order_returns_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `purchase_order_returns_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `purchase_order_returns_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD CONSTRAINT `sales_orders_cashier_id_foreign` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `sales_orders_cashier_session_id_foreign` FOREIGN KEY (`cashier_session_id`) REFERENCES `cashier_sessions` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `sales_orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `sales_orders_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_orders_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sales_order_details`
--
ALTER TABLE `sales_order_details`
  ADD CONSTRAINT `sales_order_details_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_order_details_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_order_details_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `sales_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_order_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `sales_order_details_return_id_foreign` FOREIGN KEY (`return_id`) REFERENCES `sales_order_returns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_order_details_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sales_order_payments`
--
ALTER TABLE `sales_order_payments`
  ADD CONSTRAINT `sales_order_payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_order_payments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `sales_order_payments_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_order_payments_finance_account_id_foreign` FOREIGN KEY (`finance_account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `sales_order_payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `sales_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_order_payments_return_id_foreign` FOREIGN KEY (`return_id`) REFERENCES `sales_order_returns` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_order_returns`
--
ALTER TABLE `sales_order_returns`
  ADD CONSTRAINT `sales_order_returns_cashier_id_foreign` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `sales_order_returns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_order_returns_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `sales_order_returns_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_order_returns_sales_order_id_foreign` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `sales_order_returns_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `settings`
--
ALTER TABLE `settings`
  ADD CONSTRAINT `settings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `settings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  ADD CONSTRAINT `stock_adjustments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_adjustments_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_adjustments_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `stock_adjustment_details`
--
ALTER TABLE `stock_adjustment_details`
  ADD CONSTRAINT `stock_adjustment_details_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `stock_adjustments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_adjustment_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_movements_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD CONSTRAINT `suppliers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `suppliers_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `suppliers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `supplier_ledgers`
--
ALTER TABLE `supplier_ledgers`
  ADD CONSTRAINT `supplier_ledgers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `supplier_ledgers_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `supplier_ledgers_finance_account_id_foreign` FOREIGN KEY (`finance_account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `supplier_ledgers_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `supplier_ledgers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `supplier_wallet_transactions`
--
ALTER TABLE `supplier_wallet_transactions`
  ADD CONSTRAINT `supplier_wallet_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `supplier_wallet_transactions_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `supplier_wallet_transactions_finance_account_id_foreign` FOREIGN KEY (`finance_account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `supplier_wallet_transactions_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `supplier_wallet_transactions_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tax_schemes`
--
ALTER TABLE `tax_schemes`
  ADD CONSTRAINT `tax_schemes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tax_schemes_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tax_schemes_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
