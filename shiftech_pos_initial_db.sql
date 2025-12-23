-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: shiftech_amanah_pos
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `acl_model_has_permissions`
--

DROP TABLE IF EXISTS `acl_model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `acl_model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `acl_model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `acl_permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_model_has_permissions`
--

LOCK TABLES `acl_model_has_permissions` WRITE;
/*!40000 ALTER TABLE `acl_model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `acl_model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_model_has_roles`
--

DROP TABLE IF EXISTS `acl_model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `acl_model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `acl_model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `acl_roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_model_has_roles`
--

LOCK TABLES `acl_model_has_roles` WRITE;
/*!40000 ALTER TABLE `acl_model_has_roles` DISABLE KEYS */;
INSERT INTO `acl_model_has_roles` VALUES (1,'App\\Models\\User',2);
/*!40000 ALTER TABLE `acl_model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_permissions`
--

DROP TABLE IF EXISTS `acl_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `acl_permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `acl_permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_permissions`
--

LOCK TABLES `acl_permissions` WRITE;
/*!40000 ALTER TABLE `acl_permissions` DISABLE KEYS */;
INSERT INTO `acl_permissions` VALUES (1,'admin.cashier-session.index','web','Lihat Daftar Sesi Kasir','Manajemen Sesi Kasir','2025-12-23 05:46:31','2025-12-23 05:46:31'),(2,'admin.cashier-session.detail','web','Lihat Detail Sesi Kasir','Manajemen Sesi Kasir','2025-12-23 05:46:31','2025-12-23 05:46:31'),(3,'admin.cashier-session.open','web','Buka Sesi Kasir','Manajemen Sesi Kasir','2025-12-23 05:46:31','2025-12-23 05:46:31'),(4,'admin.cashier-session.close','web','Tutup Sesi Kasir','Manajemen Sesi Kasir','2025-12-23 05:46:31','2025-12-23 05:46:31'),(5,'admin.cashier-session.delete','web','Hapus Sesi Kasir','Manajemen Sesi Kasir','2025-12-23 05:46:31','2025-12-23 05:46:31'),(6,'admin.cashier-terminal.index','web','Lihat Daftar Terminal Kasir','Manajemen Terminal Kasir','2025-12-23 05:46:31','2025-12-23 05:46:31'),(7,'admin.cashier-terminal.detail','web','Lihat Detail Terminal Kasir','Manajemen Terminal Kasir','2025-12-23 05:46:31','2025-12-23 05:46:31'),(8,'admin.cashier-terminal.add','web','Tambah Terminal Kasir','Manajemen Terminal Kasir','2025-12-23 05:46:31','2025-12-23 05:46:31'),(9,'admin.cashier-terminal.edit','web','Edit Terminal Kasir','Manajemen Terminal Kasir','2025-12-23 05:46:31','2025-12-23 05:46:31'),(10,'admin.cashier-terminal.delete','web','Hapus Terminal Kasir','Manajemen Terminal Kasir','2025-12-23 05:46:31','2025-12-23 05:46:31'),(11,'admin.cashier-cash-drop.index','web','Lihat Daftar Setoran Kas','Manajemen Setoran Kas','2025-12-23 05:46:31','2025-12-23 05:46:31'),(12,'admin.cashier-cash-drop.detail','web','Lihat Detail Setoran Kas','Manajemen Setoran Kas','2025-12-23 05:46:31','2025-12-23 05:46:31'),(13,'admin.cashier-cash-drop.add','web','Buat Pengajuan Setoran Kas','Manajemen Setoran Kas','2025-12-23 05:46:31','2025-12-23 05:46:31'),(14,'admin.cashier-cash-drop.confirm','web','Konfirmasi Setoran Kas','Manajemen Setoran Kas','2025-12-23 05:46:31','2025-12-23 05:46:31'),(15,'admin.cashier-cash-drop.delete','web','Hapus Setoran Kas','Manajemen Setoran Kas','2025-12-23 05:46:31','2025-12-23 05:46:31'),(16,'admin.customer.index','web','Lihat Daftar Pelanggan','Manajemen Pelanggan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(17,'admin.customer.detail','web','Lihat Detail Pelanggan','Manajemen Pelanggan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(18,'admin.customer.add','web','Tambah / Duplikat Pelanggan','Manajemen Pelanggan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(19,'admin.customer.edit','web','Edit Pelanggan','Manajemen Pelanggan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(20,'admin.customer.delete','web','Hapus Pelanggan','Manajemen Pelanggan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(21,'admin.customer.import','web','Impor Data Pelanggan','Manajemen Pelanggan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(22,'admin.customer-ledger.index','web','Lihat Daftar Transaksi','Manajemen Utang / Piutang Pelanggan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(23,'admin.customer-ledger.detail','web','Lihat Detail Transaksi','Manajemen Utang / Piutang Pelanggan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(24,'admin.customer-ledger.add','web','Tambah Transaksi','Manajemen Utang / Piutang Pelanggan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(25,'admin.customer-ledger.adjust','web','Menyesuaikan Transaksi','Manajemen Utang / Piutang Pelanggan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(26,'admin.customer-ledger.delete','web','Hapus Transaksi','Manajemen Utang / Piutang Pelanggan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(27,'admin.customer-wallet-transaction.index','web','Lihat Daftar Transaksi Deposit','Manajemen Deposit Pelanggan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(28,'admin.customer-wallet-transaction.detail','web','Lihat Detail Transaksi Deposit','Manajemen Deposit Pelanggan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(29,'admin.customer-wallet-transaction.add','web','Tambah Transaksi Deposit','Manajemen Deposit Pelanggan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(30,'admin.customer-wallet-transaction.delete','web','Hapus Transaksi Deposit','Manajemen Deposit Pelanggan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(31,'admin.customer-wallet-transaction-confirmation.index','web','Lihat Daftar Konfirmasi Deposit','Manajemen Konfirmasi Deposit Pelanggan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(32,'admin.customer-wallet-transaction-confirmation.detail','web','Lihat Detail Konfirmasi Deposit','Manajemen Konfirmasi Deposit Pelanggan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(33,'admin.customer-wallet-transaction-confirmation:accept','web','Setujui Konfirmasi Deposit','Manajemen Konfirmasi Deposit Pelanggan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(34,'admin.customer-wallet-transaction-confirmation:deny','web','Tolak Konfirmasi Deposit','Manajemen Konfirmasi Deposit Pelanggan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(35,'admin.customer-wallet-transaction-confirmation.delete','web','Hapus Konfirmasi Deposit','Manajemen Konfirmasi Deposit Pelanggan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(36,'admin.supplier-wallet-transaction.index','web','Lihat Daftar Transaksi Deposit','Manajemen Deposit Supplier','2025-12-23 05:46:31','2025-12-23 05:46:31'),(37,'admin.supplier-wallet-transaction.detail','web','Lihat Detail Transaksi Deposit','Manajemen Deposit Supplier','2025-12-23 05:46:31','2025-12-23 05:46:31'),(38,'admin.supplier-wallet-transaction.add','web','Tambah Transaksi Deposit','Manajemen Deposit Supplier','2025-12-23 05:46:31','2025-12-23 05:46:31'),(39,'admin.supplier-wallet-transaction.delete','web','Hapus Transaksi Deposit','Manajemen Deposit Supplier','2025-12-23 05:46:31','2025-12-23 05:46:31'),(40,'admin.supplier-ledger.index','web','Lihat Daftar Transaksi','Manajemen Utang / Piutang Supplier','2025-12-23 05:46:31','2025-12-23 05:46:31'),(41,'admin.supplier-ledger.detail','web','Lihat Detail Transaksi','Manajemen Utang / Piutang Supplier','2025-12-23 05:46:31','2025-12-23 05:46:31'),(42,'admin.supplier-ledger.add','web','Tambah Transaksi','Manajemen Utang / Piutang Supplier','2025-12-23 05:46:31','2025-12-23 05:46:31'),(43,'admin.supplier-ledger.adjust','web','Menyesuaikan Transaksi','Manajemen Utang / Piutang Supplier','2025-12-23 05:46:31','2025-12-23 05:46:31'),(44,'admin.supplier-ledger.delete','web','Hapus Transaksi','Manajemen Utang / Piutang Supplier','2025-12-23 05:46:31','2025-12-23 05:46:31'),(45,'admin.product.index','web','Lihat Daftar Produk','Manajemen Produk','2025-12-23 05:46:31','2025-12-23 05:46:31'),(46,'admin.product.detail','web','Lihat Detail Produk','Manajemen Produk','2025-12-23 05:46:31','2025-12-23 05:46:31'),(47,'admin.product.add','web','Tambah / Duplikat Produk','Manajemen Produk','2025-12-23 05:46:31','2025-12-23 05:46:31'),(48,'admin.product.edit','web','Edit Produk','Manajemen Produk','2025-12-23 05:46:31','2025-12-23 05:46:31'),(49,'admin.product.delete','web','Hapus Produk','Manajemen Produk','2025-12-23 05:46:31','2025-12-23 05:46:31'),(50,'admin.product.import','web','Impor Data Produk','Manajemen Produk','2025-12-23 05:46:31','2025-12-23 05:46:31'),(51,'admin.product:view-cost','web','Lihat Modal','Manajemen Produk','2025-12-23 05:46:31','2025-12-23 05:46:31'),(52,'admin.product:view-supplier','web','Lihat Supplier','Manajemen Produk','2025-12-23 05:46:31','2025-12-23 05:46:31'),(53,'admin.product-category.index','web','Lihat Daftar Kategori Produk','Manajemen Kategori Produk','2025-12-23 05:46:31','2025-12-23 05:46:31'),(54,'admin.product-category.add','web','Tambah / Duplikat Kategori Produk','Manajemen Kategori Produk','2025-12-23 05:46:31','2025-12-23 05:46:31'),(55,'admin.product-category.edit','web','Edit Kategori Produk','Manajemen Kategori Produk','2025-12-23 05:46:31','2025-12-23 05:46:31'),(56,'admin.product-category.delete','web','Hapus Kategori Produk','Manajemen Kategori Produk','2025-12-23 05:46:31','2025-12-23 05:46:31'),(57,'admin.stock-movement.index','web','Lihat Daftar Pergerakan Stok','Manajemen Stok','2025-12-23 05:46:31','2025-12-23 05:46:31'),(58,'admin.stock-adjustment.index','web','Lihat Daftar Penyesuaian Stok','Manajemen Stok','2025-12-23 05:46:31','2025-12-23 05:46:31'),(59,'admin.stock-adjustment.detail','web','Lihat Detail Penyesuaian Stok','Manajemen Stok','2025-12-23 05:46:31','2025-12-23 05:46:31'),(60,'admin.stock-adjustment.save','web','Simpan Penyesuaian Stok','Manajemen Stok','2025-12-23 05:46:31','2025-12-23 05:46:31'),(61,'admin.stock-adjustment.delete','web','Hapus Penyesuaian Stok','Manajemen Stok','2025-12-23 05:46:31','2025-12-23 05:46:31'),(62,'admin.finance-account.index','web','Lihat Daftar Akun Keuangan','Manajemen Akun Keuangan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(63,'admin.finance-account.detail','web','Lihat Detail Akun Keuangan','Manajemen Akun Keuangan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(64,'admin.finance-account.add','web','Tambah / Duplikat Akun Keuangan','Manajemen Akun Keuangan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(65,'admin.finance-account.edit','web','Edit Akun Keuangan','Manajemen Akun Keuangan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(66,'admin.finance-account.delete','web','Hapus Akun Keuangan','Manajemen Akun Keuangan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(67,'admin.finance-transaction.index','web','Lihat Daftar Transaksi Keuangan','Manajemen Transaksi Keuangan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(68,'admin.finance-transaction.detail','web','Lihat Detail Transaksi Keuangan','Manajemen Transaksi Keuangan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(69,'admin.finance-transaction.add','web','Tambah / Duplikat Transaksi Keuangan','Manajemen Transaksi Keuangan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(70,'admin.finance-transaction.edit','web','Edit Transaksi Keuangan','Manajemen Transaksi Keuangan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(71,'admin.finance-transaction.delete','web','Hapus Transaksi Keuangan','Manajemen Transaksi Keuangan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(72,'admin.finance-transaction-category.index','web','Lihat Daftar Kategori Transaksi','Manajemen Kategori Transaksi Keuangan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(73,'admin.finance-transaction-category.add','web','Tambah / Duplikat Kategori Transaksi','Manajemen Kategori Transaksi Keuangan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(74,'admin.finance-transaction-category.edit','web','Edit Kategori Transaksi','Manajemen Kategori Transaksi Keuangan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(75,'admin.finance-transaction-category.delete','web','Hapus Kategori Transaksi','Manajemen Kategori Transaksi Keuangan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(76,'admin.operational-cost.index','web','Lihat Daftar Biaya Operasional','Manajemen Biaya Operasional','2025-12-23 05:46:31','2025-12-23 05:46:31'),(77,'admin.operational-cost.detail','web','Lihat Detail Biaya Operasional','Manajemen Biaya Operasional','2025-12-23 05:46:31','2025-12-23 05:46:31'),(78,'admin.operational-cost.add','web','Tambah / Duplikat Biaya Operasional','Manajemen Biaya Operasional','2025-12-23 05:46:31','2025-12-23 05:46:31'),(79,'admin.operational-cost.edit','web','Edit Biaya Operasional','Manajemen Biaya Operasional','2025-12-23 05:46:31','2025-12-23 05:46:31'),(80,'admin.operational-cost.delete','web','Hapus Biaya Operasional','Manajemen Biaya Operasional','2025-12-23 05:46:31','2025-12-23 05:46:31'),(81,'admin.operational-cost-category.index','web','Lihat Daftar Kategori Biaya Operasional','Manajemen Kategori Biaya Operasional','2025-12-23 05:46:31','2025-12-23 05:46:31'),(82,'admin.operational-cost-category.add','web','Tambah / Duplikat Kategori Biaya Operasional','Manajemen Kategori Biaya Operasional','2025-12-23 05:46:31','2025-12-23 05:46:31'),(83,'admin.operational-cost-category.edit','web','Edit Kategori Biaya Operasional','Manajemen Kategori Biaya Operasional','2025-12-23 05:46:31','2025-12-23 05:46:31'),(84,'admin.operational-cost-category.delete','web','Hapus Kategori Biaya Operasional','Manajemen Kategori Biaya Operasional','2025-12-23 05:46:31','2025-12-23 05:46:31'),(85,'admin.purchase-order.index','web','Lihat Daftar Pembelian','Manajemen Pembelian','2025-12-23 05:46:31','2025-12-23 05:46:31'),(86,'admin.purchase-order.detail','web','Lihat Detail Pembelian','Manajemen Pembelian','2025-12-23 05:46:31','2025-12-23 05:46:31'),(87,'admin.purchase-order.edit','web','Membuat / Edit Pembelian','Manajemen Pembelian','2025-12-23 05:46:31','2025-12-23 05:46:31'),(88,'admin.purchase-order.delete','web','Hapus Pembelian','Manajemen Pembelian','2025-12-23 05:46:31','2025-12-23 05:46:31'),(89,'admin.purchase-order.cancel','web','Batalkan Pembelian','Manajemen Pembelian','2025-12-23 05:46:31','2025-12-23 05:46:31'),(90,'admin.purchase-order.close','web','Tutup Pembelian','Manajemen Pembelian','2025-12-23 05:46:31','2025-12-23 05:46:31'),(91,'admin.purchase-order.add-payment','web','Tambah Pembayaran','Manajemen Pembelian','2025-12-23 05:46:31','2025-12-23 05:46:31'),(92,'admin.purchase-order.delete-payment','web','Hapus Pembayaran','Manajemen Pembelian','2025-12-23 05:46:31','2025-12-23 05:46:31'),(93,'admin.purchase-order-return.index','web','Lihat Daftar Retur Pembelian','Manajemen Retur Pembelian','2025-12-23 05:46:31','2025-12-23 05:46:31'),(94,'admin.purchase-order-return.detail','web','Lihat Detail Retur Pembelian','Manajemen Retur Pembelian','2025-12-23 05:46:31','2025-12-23 05:46:31'),(95,'admin.purchase-order-return.edit','web','Membuat / Edit Retur Pembelian','Manajemen Retur Pembelian','2025-12-23 05:46:31','2025-12-23 05:46:31'),(96,'admin.purchase-order-return.delete','web','Hapus Retur Pembelian','Manajemen Retur Pembelian','2025-12-23 05:46:31','2025-12-23 05:46:31'),(97,'admin.purchase-order-return.cancel','web','Batalkan Retur Pembelian','Manajemen Retur Pembelian','2025-12-23 05:46:31','2025-12-23 05:46:31'),(98,'admin.purchase-order-return.close','web','Tutup Retur Pembelian','Manajemen Retur Pembelian','2025-12-23 05:46:31','2025-12-23 05:46:31'),(99,'admin.purchase-order-return.add-refund','web','Tambah Refund Pembayaran','Manajemen Retur Pembelian','2025-12-23 05:46:31','2025-12-23 05:46:31'),(100,'admin.purchase-order-return.delete-refund','web','Hapus Refund Pembayaran','Manajemen Retur Pembelian','2025-12-23 05:46:31','2025-12-23 05:46:31'),(101,'admin.sales-order.index','web','Lihat Daftar Penjualan','Manajemen Penjualan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(102,'admin.sales-order.detail','web','Lihat Detail Penjualan','Manajemen Penjualan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(103,'admin.sales-order.edit','web','Membuat / Edit Penjualan','Manajemen Penjualan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(104,'admin.sales-order.editor:edit-price','web','Edit Harga Jual','Manajemen Penjualan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(105,'admin.sales-order.delete','web','Hapus Penjualan','Manajemen Penjualan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(106,'admin.sales-order.cancel','web','Batalkan Penjualan','Manajemen Penjualan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(107,'admin.sales-order.close','web','Tutup Penjualan','Manajemen Penjualan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(108,'admin.sales-order.add-payment','web','Tambah Pembayaran','Manajemen Penjualan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(109,'admin.sales-order.delete-payment','web','Hapus Pembayaran','Manajemen Penjualan','2025-12-23 05:46:31','2025-12-23 05:46:31'),(110,'admin.sales-order-return.index','web','Lihat Daftar Retur Penjualan','Manajemen Retur Penjual','2025-12-23 05:46:31','2025-12-23 05:46:31'),(111,'admin.sales-order-return.detail','web','Lihat Detail Retur Penjualan','Manajemen Retur Penjual','2025-12-23 05:46:31','2025-12-23 05:46:31'),(112,'admin.sales-order-return.edit','web','Membuat / Edit Retur Penjualan','Manajemen Retur Penjual','2025-12-23 05:46:31','2025-12-23 05:46:31'),(113,'admin.sales-order-return.delete','web','Hapus Retur Penjualan','Manajemen Retur Penjual','2025-12-23 05:46:31','2025-12-23 05:46:31'),(114,'admin.sales-order-return.cancel','web','Batalkan Retur Penjualan','Manajemen Retur Penjual','2025-12-23 05:46:31','2025-12-23 05:46:31'),(115,'admin.sales-order-return.close','web','Tutup Retur Penjualan','Manajemen Retur Penjual','2025-12-23 05:46:31','2025-12-23 05:46:31'),(116,'admin.sales-order-return.add-refund','web','Tambah Refund Pembayaran','Manajemen Retur Penjual','2025-12-23 05:46:31','2025-12-23 05:46:31'),(117,'admin.sales-order-return.delete-refund','web','Hapus Refund Pembayaran','Manajemen Retur Penjual','2025-12-23 05:46:31','2025-12-23 05:46:31'),(118,'admin.supplier.index','web','Lihat Daftar Pemasok','Manajemen Pemasok','2025-12-23 05:46:31','2025-12-23 05:46:31'),(119,'admin.supplier.detail','web','Lihat Detail Pemasok','Manajemen Pemasok','2025-12-23 05:46:31','2025-12-23 05:46:31'),(120,'admin.supplier.add','web','Tambah / Duplikat Pemasok','Manajemen Pemasok','2025-12-23 05:46:31','2025-12-23 05:46:31'),(121,'admin.supplier.edit','web','Edit Pemasok','Manajemen Pemasok','2025-12-23 05:46:31','2025-12-23 05:46:31'),(122,'admin.supplier.delete','web','Hapus Pemasok','Manajemen Pemasok','2025-12-23 05:46:31','2025-12-23 05:46:31'),(123,'admin.user.index','web','Lihat Daftar Pengguna','Manajemen Pengguna','2025-12-23 05:46:31','2025-12-23 05:46:31'),(124,'admin.user.detail','web','Lihat Detail Pengguna','Manajemen Pengguna','2025-12-23 05:46:31','2025-12-23 05:46:31'),(125,'admin.user.add','web','Tambah / Duplikat Pengguna','Manajemen Pengguna','2025-12-23 05:46:31','2025-12-23 05:46:31'),(126,'admin.user.edit','web','Edit Pengguna','Manajemen Pengguna','2025-12-23 05:46:31','2025-12-23 05:46:31'),(127,'admin.user.delete','web','Hapus Pengguna','Manajemen Pengguna','2025-12-23 05:46:31','2025-12-23 05:46:31'),(128,'admin.user-role.index','web','Lihat Daftar Peran Pengguna','Manajemen Peran Pengguna','2025-12-23 05:46:31','2025-12-23 05:46:31'),(129,'admin.user-role.detail','web','Lihat Detail Peran Pengguna','Manajemen Peran Pengguna','2025-12-23 05:46:31','2025-12-23 05:46:31'),(130,'admin.user-role.add','web','Tambah Peran Pengguna','Manajemen Peran Pengguna','2025-12-23 05:46:31','2025-12-23 05:46:31'),(131,'admin.user-role.edit','web','Edit Peran Pengguna','Manajemen Peran Pengguna','2025-12-23 05:46:31','2025-12-23 05:46:31'),(132,'admin.user-role.delete','web','Hapus Peran Pengguna','Manajemen Peran Pengguna','2025-12-23 05:46:31','2025-12-23 05:46:31'),(133,'admin.company-profile.edit','web','Edit Profil Perusahaan','Pengaturan','2025-12-23 05:46:31','2025-12-23 05:46:31');
/*!40000 ALTER TABLE `acl_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_role_has_permissions`
--

DROP TABLE IF EXISTS `acl_role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `acl_role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `acl_role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `acl_role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `acl_permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `acl_role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `acl_roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_role_has_permissions`
--

LOCK TABLES `acl_role_has_permissions` WRITE;
/*!40000 ALTER TABLE `acl_role_has_permissions` DISABLE KEYS */;
INSERT INTO `acl_role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(6,1),(7,1),(16,1),(17,1),(18,1),(19,1),(45,1),(46,1),(53,1),(57,1),(67,1),(68,1),(69,1),(76,1),(77,1),(78,1),(81,1),(82,1),(101,1),(102,1),(103,1),(124,1);
/*!40000 ALTER TABLE `acl_role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_roles`
--

DROP TABLE IF EXISTS `acl_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `acl_roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `acl_roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_roles`
--

LOCK TABLES `acl_roles` WRITE;
/*!40000 ALTER TABLE `acl_roles` DISABLE KEYS */;
INSERT INTO `acl_roles` VALUES (1,'Kasir',NULL,'web','2025-12-23 05:46:31','2025-12-23 05:46:31');
/*!40000 ALTER TABLE `acl_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cashier_cash_drops`
--

DROP TABLE IF EXISTS `cashier_cash_drops`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cashier_cash_drops` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datetime` datetime NOT NULL,
  `cashier_id` bigint unsigned NOT NULL,
  `terminal_id` bigint unsigned DEFAULT NULL,
  `cashier_session_id` bigint unsigned DEFAULT NULL,
  `source_finance_account_id` bigint unsigned NOT NULL,
  `target_finance_account_id` bigint unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cashier_cash_drops_code_unique` (`code`),
  KEY `cashier_cash_drops_cashier_id_foreign` (`cashier_id`),
  KEY `cashier_cash_drops_terminal_id_foreign` (`terminal_id`),
  KEY `cashier_cash_drops_cashier_session_id_foreign` (`cashier_session_id`),
  KEY `cashier_cash_drops_source_finance_account_id_foreign` (`source_finance_account_id`),
  KEY `cashier_cash_drops_target_finance_account_id_foreign` (`target_finance_account_id`),
  KEY `cashier_cash_drops_approved_by_foreign` (`approved_by`),
  KEY `cashier_cash_drops_created_by_foreign` (`created_by`),
  KEY `cashier_cash_drops_updated_by_foreign` (`updated_by`),
  KEY `cashier_cash_drops_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `cashier_cash_drops_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `cashier_cash_drops_cashier_id_foreign` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`),
  CONSTRAINT `cashier_cash_drops_cashier_session_id_foreign` FOREIGN KEY (`cashier_session_id`) REFERENCES `cashier_sessions` (`id`),
  CONSTRAINT `cashier_cash_drops_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cashier_cash_drops_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cashier_cash_drops_source_finance_account_id_foreign` FOREIGN KEY (`source_finance_account_id`) REFERENCES `finance_accounts` (`id`),
  CONSTRAINT `cashier_cash_drops_target_finance_account_id_foreign` FOREIGN KEY (`target_finance_account_id`) REFERENCES `finance_accounts` (`id`),
  CONSTRAINT `cashier_cash_drops_terminal_id_foreign` FOREIGN KEY (`terminal_id`) REFERENCES `cashier_terminals` (`id`),
  CONSTRAINT `cashier_cash_drops_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cashier_cash_drops`
--

LOCK TABLES `cashier_cash_drops` WRITE;
/*!40000 ALTER TABLE `cashier_cash_drops` DISABLE KEYS */;
/*!40000 ALTER TABLE `cashier_cash_drops` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cashier_session_transactions`
--

DROP TABLE IF EXISTS `cashier_session_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cashier_session_transactions` (
  `cashier_session_id` bigint unsigned NOT NULL,
  `finance_transaction_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`cashier_session_id`,`finance_transaction_id`),
  KEY `cashier_session_transactions_finance_transaction_id_foreign` (`finance_transaction_id`),
  CONSTRAINT `cashier_session_transactions_cashier_session_id_foreign` FOREIGN KEY (`cashier_session_id`) REFERENCES `cashier_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cashier_session_transactions_finance_transaction_id_foreign` FOREIGN KEY (`finance_transaction_id`) REFERENCES `finance_transactions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cashier_session_transactions`
--

LOCK TABLES `cashier_session_transactions` WRITE;
/*!40000 ALTER TABLE `cashier_session_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `cashier_session_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cashier_sessions`
--

DROP TABLE IF EXISTS `cashier_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cashier_sessions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `cashier_terminal_id` bigint unsigned NOT NULL,
  `opening_balance` decimal(15,2) NOT NULL,
  `closing_balance` decimal(15,2) DEFAULT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT '0',
  `opened_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `opening_notes` text COLLATE utf8mb4_unicode_ci,
  `closing_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cashier_sessions_created_by_foreign` (`created_by`),
  KEY `cashier_sessions_deleted_by_foreign` (`deleted_by`),
  KEY `cashier_sessions_user_id_index` (`user_id`),
  KEY `cashier_sessions_cashier_terminal_id_index` (`cashier_terminal_id`),
  KEY `cashier_sessions_is_closed_index` (`is_closed`),
  KEY `cashier_sessions_opened_at_index` (`opened_at`),
  KEY `cashier_sessions_closed_at_index` (`closed_at`),
  CONSTRAINT `cashier_sessions_cashier_terminal_id_foreign` FOREIGN KEY (`cashier_terminal_id`) REFERENCES `cashier_terminals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cashier_sessions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cashier_sessions_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cashier_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cashier_sessions`
--

LOCK TABLES `cashier_sessions` WRITE;
/*!40000 ALTER TABLE `cashier_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `cashier_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cashier_terminals`
--

DROP TABLE IF EXISTS `cashier_terminals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cashier_terminals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `finance_account_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cashier_terminals_finance_account_id_unique` (`finance_account_id`),
  UNIQUE KEY `cashier_terminals_name_unique` (`name`),
  KEY `cashier_terminals_created_by_foreign` (`created_by`),
  KEY `cashier_terminals_deleted_by_foreign` (`deleted_by`),
  KEY `cashier_terminals_active_index` (`active`),
  CONSTRAINT `cashier_terminals_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cashier_terminals_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cashier_terminals_finance_account_id_foreign` FOREIGN KEY (`finance_account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cashier_terminals`
--

LOCK TABLES `cashier_terminals` WRITE;
/*!40000 ALTER TABLE `cashier_terminals` DISABLE KEYS */;
INSERT INTO `cashier_terminals` VALUES (1,2,'Kasir Toko 1','Toko','Auto generated cashier terminal',1,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `cashier_terminals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_ledgers`
--

DROP TABLE IF EXISTS `customer_ledgers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_ledgers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned NOT NULL,
  `finance_account_id` bigint unsigned DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_id` bigint unsigned DEFAULT NULL,
  `datetime` datetime NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `running_balance` decimal(12,2) DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_ledgers_code_unique` (`code`),
  KEY `customer_ledgers_customer_id_foreign` (`customer_id`),
  KEY `customer_ledgers_finance_account_id_foreign` (`finance_account_id`),
  KEY `customer_ledgers_ref_type_ref_id_index` (`ref_type`,`ref_id`),
  KEY `customer_ledgers_created_by_foreign` (`created_by`),
  KEY `customer_ledgers_updated_by_foreign` (`updated_by`),
  KEY `customer_ledgers_deleted_by_foreign` (`deleted_by`),
  KEY `customer_ledgers_datetime_index` (`datetime`),
  KEY `customer_ledgers_type_index` (`type`),
  CONSTRAINT `customer_ledgers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customer_ledgers_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `customer_ledgers_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customer_ledgers_finance_account_id_foreign` FOREIGN KEY (`finance_account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `customer_ledgers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_ledgers`
--

LOCK TABLES `customer_ledgers` WRITE;
/*!40000 ALTER TABLE `customer_ledgers` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_ledgers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_notifications`
--

DROP TABLE IF EXISTS `customer_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_notifications_customer_id_read_at_index` (`customer_id`,`read_at`),
  CONSTRAINT `customer_notifications_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_notifications`
--

LOCK TABLES `customer_notifications` WRITE;
/*!40000 ALTER TABLE `customer_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_password_reset_tokens`
--

DROP TABLE IF EXISTS `customer_password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_password_reset_tokens` (
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_password_reset_tokens`
--

LOCK TABLES `customer_password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `customer_password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_sessions`
--

DROP TABLE IF EXISTS `customer_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_account_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_sessions_customer_account_id_index` (`customer_account_id`),
  KEY `customer_sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_sessions`
--

LOCK TABLES `customer_sessions` WRITE;
/*!40000 ALTER TABLE `customer_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_settings`
--

DROP TABLE IF EXISTS `customer_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_settings` (
  `customer_id` bigint unsigned NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`customer_id`,`key`),
  CONSTRAINT `customer_settings_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_settings`
--

LOCK TABLES `customer_settings` WRITE;
/*!40000 ALTER TABLE `customer_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_wallet_transactions`
--

DROP TABLE IF EXISTS `customer_wallet_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_wallet_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `finance_account_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ref_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_id` bigint unsigned DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_wallet_transactions_code_unique` (`code`),
  KEY `customer_wallet_transactions_finance_account_id_foreign` (`finance_account_id`),
  KEY `customer_wallet_transactions_customer_id_foreign` (`customer_id`),
  KEY `customer_wallet_transactions_ref_type_ref_id_index` (`ref_type`,`ref_id`),
  KEY `customer_wallet_transactions_created_by_foreign` (`created_by`),
  KEY `customer_wallet_transactions_updated_by_foreign` (`updated_by`),
  KEY `customer_wallet_transactions_deleted_by_foreign` (`deleted_by`),
  KEY `customer_wallet_transactions_datetime_index` (`datetime`),
  KEY `customer_wallet_transactions_type_index` (`type`),
  CONSTRAINT `customer_wallet_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customer_wallet_transactions_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `customer_wallet_transactions_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customer_wallet_transactions_finance_account_id_foreign` FOREIGN KEY (`finance_account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `customer_wallet_transactions_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_wallet_transactions`
--

LOCK TABLES `customer_wallet_transactions` WRITE;
/*!40000 ALTER TABLE `customer_wallet_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_wallet_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_wallet_trx_confirmations`
--

DROP TABLE IF EXISTS `customer_wallet_trx_confirmations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_wallet_trx_confirmations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `finance_account_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datetime` datetime DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_wallet_trx_confirmations_code_unique` (`code`),
  KEY `customer_wallet_trx_confirmations_finance_account_id_foreign` (`finance_account_id`),
  KEY `customer_wallet_trx_confirmations_customer_id_foreign` (`customer_id`),
  KEY `customer_wallet_trx_confirmations_created_by_foreign` (`created_by`),
  KEY `customer_wallet_trx_confirmations_updated_by_foreign` (`updated_by`),
  KEY `customer_wallet_trx_confirmations_deleted_by_foreign` (`deleted_by`),
  KEY `customer_wallet_trx_confirmations_datetime_index` (`datetime`),
  KEY `customer_wallet_trx_confirmations_status_index` (`status`),
  CONSTRAINT `customer_wallet_trx_confirmations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customer_wallet_trx_confirmations_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `customer_wallet_trx_confirmations_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customer_wallet_trx_confirmations_finance_account_id_foreign` FOREIGN KEY (`finance_account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `customer_wallet_trx_confirmations_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_wallet_trx_confirmations`
--

LOCK TABLES `customer_wallet_trx_confirmations` WRITE;
/*!40000 ALTER TABLE `customer_wallet_trx_confirmations` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_wallet_trx_confirmations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
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
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_code_unique` (`code`),
  KEY `customers_created_by_foreign` (`created_by`),
  KEY `customers_updated_by_foreign` (`updated_by`),
  KEY `customers_deleted_by_foreign` (`deleted_by`),
  KEY `customers_type_index` (`type`),
  KEY `customers_name_index` (`name`),
  KEY `customers_email_index` (`email`),
  KEY `customers_phone_index` (`phone`),
  KEY `customers_wallet_balance_index` (`wallet_balance`),
  KEY `customers_balance_index` (`balance`),
  KEY `customers_active_index` (`active`),
  KEY `customers_last_login_datetime_index` (`last_login_datetime`),
  CONSTRAINT `customers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customers_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_versions`
--

DROP TABLE IF EXISTS `document_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_versions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `document_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_id` bigint unsigned NOT NULL,
  `version` int unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `changelog` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Deskripsi singkat perubahan (opsional)',
  `is_deleted` tinyint(1) NOT NULL,
  `data` json NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_versions_document_type_document_id_version_unique` (`document_type`,`document_id`,`version`),
  KEY `document_versions_document_type_document_id_index` (`document_type`,`document_id`),
  KEY `document_versions_created_by_foreign` (`created_by`),
  KEY `document_versions_is_deleted_index` (`is_deleted`),
  CONSTRAINT `document_versions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_versions`
--

LOCK TABLES `document_versions` WRITE;
/*!40000 ALTER TABLE `document_versions` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `finance_accounts`
--

DROP TABLE IF EXISTS `finance_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `finance_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
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
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  `show_in_cashier_cash_drop` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `finance_accounts_name_unique` (`name`),
  KEY `finance_accounts_created_by_foreign` (`created_by`),
  KEY `finance_accounts_updated_by_foreign` (`updated_by`),
  KEY `finance_accounts_deleted_by_foreign` (`deleted_by`),
  KEY `finance_accounts_type_index` (`type`),
  KEY `finance_accounts_balance_index` (`balance`),
  KEY `finance_accounts_active_index` (`active`),
  KEY `finance_accounts_show_in_pos_payment_index` (`show_in_pos_payment`),
  KEY `finance_accounts_show_in_purchasing_payment_index` (`show_in_purchasing_payment`),
  KEY `finance_accounts_has_wallet_access_index` (`has_wallet_access`),
  CONSTRAINT `finance_accounts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `finance_accounts_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `finance_accounts_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `finance_accounts`
--

LOCK TABLES `finance_accounts` WRITE;
/*!40000 ALTER TABLE `finance_accounts` DISABLE KEYS */;
INSERT INTO `finance_accounts` VALUES (1,'Kas Tunai 1','petty_cash','','','',0,1,1,1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),(2,'Kas Kasir 1','cashier_cash','','','',0,1,0,0,0,NULL,'2025-12-23 12:46:32',NULL,NULL,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `finance_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `finance_transaction_categories`
--

DROP TABLE IF EXISTS `finance_transaction_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `finance_transaction_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `finance_transaction_categories_name_unique` (`name`),
  KEY `finance_transaction_categories_created_by_foreign` (`created_by`),
  KEY `finance_transaction_categories_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `finance_transaction_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `finance_transaction_categories_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `finance_transaction_categories`
--

LOCK TABLES `finance_transaction_categories` WRITE;
/*!40000 ALTER TABLE `finance_transaction_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `finance_transaction_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `finance_transaction_tags`
--

DROP TABLE IF EXISTS `finance_transaction_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `finance_transaction_tags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `finance_transaction_tags_name_unique` (`name`),
  KEY `finance_transaction_tags_created_by_foreign` (`created_by`),
  KEY `finance_transaction_tags_updated_by_foreign` (`updated_by`),
  KEY `finance_transaction_tags_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `finance_transaction_tags_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `finance_transaction_tags_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `finance_transaction_tags_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `finance_transaction_tags`
--

LOCK TABLES `finance_transaction_tags` WRITE;
/*!40000 ALTER TABLE `finance_transaction_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `finance_transaction_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `finance_transactions`
--

DROP TABLE IF EXISTS `finance_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `finance_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `account_id` bigint unsigned DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ref_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_id` bigint unsigned DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `finance_transactions_code_unique` (`code`),
  KEY `finance_transactions_ref_type_ref_id_index` (`ref_type`,`ref_id`),
  KEY `finance_transactions_created_by_foreign` (`created_by`),
  KEY `finance_transactions_updated_by_foreign` (`updated_by`),
  KEY `finance_transactions_deleted_by_foreign` (`deleted_by`),
  KEY `finance_transactions_datetime_index` (`datetime`),
  KEY `finance_transactions_type_index` (`type`),
  KEY `finance_transactions_created_at_account_id_index` (`created_at`,`account_id`),
  KEY `finance_transactions_datetime_type_account_id_deleted_at_index` (`datetime`,`type`,`account_id`,`deleted_at`),
  KEY `finance_transactions_account_id_datetime_index` (`account_id`,`datetime`),
  KEY `finance_transactions_category_id_foreign` (`category_id`),
  CONSTRAINT `finance_transactions_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `finance_transactions_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `finance_transaction_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `finance_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `finance_transactions_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `finance_transactions_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `finance_transactions`
--

LOCK TABLES `finance_transactions` WRITE;
/*!40000 ALTER TABLE `finance_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `finance_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `finance_transactions_has_tags`
--

DROP TABLE IF EXISTS `finance_transactions_has_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `finance_transactions_has_tags` (
  `finance_transaction_id` bigint unsigned NOT NULL,
  `finance_transaction_tag_id` bigint unsigned NOT NULL,
  UNIQUE KEY `ft_tag_unique` (`finance_transaction_id`,`finance_transaction_tag_id`),
  KEY `finance_transactions_has_tags_finance_transaction_id_index` (`finance_transaction_id`),
  KEY `finance_transactions_has_tags_finance_transaction_tag_id_index` (`finance_transaction_tag_id`),
  CONSTRAINT `finance_transactions_has_tags_finance_transaction_id_foreign` FOREIGN KEY (`finance_transaction_id`) REFERENCES `finance_transactions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `finance_transactions_has_tags_finance_transaction_tag_id_foreign` FOREIGN KEY (`finance_transaction_tag_id`) REFERENCES `finance_transaction_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `finance_transactions_has_tags`
--

LOCK TABLES `finance_transactions_has_tags` WRITE;
/*!40000 ALTER TABLE `finance_transactions_has_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `finance_transactions_has_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_00_000001_create_cache_table',1),(2,'0001_01_00_000002_create_jobs_table',1),(3,'0001_01_01_000001_create_users_table',1),(4,'0001_01_01_000002_1_create_document_versions_table',1),(5,'0001_01_01_000002_create_customers_table',1),(6,'0001_01_01_000002_create_finance_accounts_table',1),(7,'0001_01_01_000002_create_finance_transactions_table',1),(8,'0001_01_01_000002_create_suppliers_table',1),(9,'0001_01_01_000002_create_tax_schemes_table',1),(10,'0001_01_01_000002_create_user_activity_logs_table',1),(11,'0001_01_01_000003_01_create_customer_wallet_transactions_table',1),(12,'0001_01_01_000003_02_create_customer_wallet_trx_confirmations_table',1),(13,'0001_01_01_000003_create_product_categories_table',1),(14,'0001_01_01_000003_create_products_table',1),(15,'0001_01_01_000004_00_create_stock_movements_table',1),(16,'0001_01_01_000004_01_create_stock_adjustments_table',1),(17,'0001_01_01_000004_02_create_stock_adjustment_details_table',1),(18,'0001_01_01_000005_01_create_cashier_terminals_table',1),(19,'0001_01_01_000005_02_create_cashier_sessions_table',1),(20,'0001_01_01_000005_03_create_cashier_session_transactions_table',1),(21,'0001_01_01_000006_create_operational_costs_category_table',1),(22,'0001_01_01_000007_create_operational_costs_table',1),(23,'0001_01_01_000008_create_settings_table',1),(24,'0001_01_01_000009_create_purchase_orders_table',1),(25,'0001_01_01_000010_create_purchase_order_returns_table',1),(26,'0001_01_01_000011_create_purchase_order_details_table',1),(27,'0001_01_01_000012_create_purchase_order_payments_table',1),(28,'0001_01_01_000013_create_sales_orders_table',1),(29,'0001_01_01_000014_create_sales_order_returns_table',1),(30,'0001_01_01_000015_create_sales_order_details_table',1),(31,'0001_01_01_000016_create_sales_order_payments_table',1),(32,'2025_07_24_111206_create_permission_tables',1),(33,'2025_07_25_150050_create_personal_access_tokens_table',1),(34,'2025_11_06_161358_create_uoms_table',1),(35,'2025_11_12_171857_create_finance_transaction_indexes_1',1),(36,'2025_11_12_172939_create_finance_transaction_indexes_2',1),(37,'2025_11_13_072617_create_finance_transaction_categories_table',1),(38,'2025_11_14_072703_create_finance_transaction_tags_table',1),(39,'2025_11_14_072919_create_finance_transactions_has_tags_table',1),(40,'2025_11_19_203036_create_product_units_table',1),(41,'2025_11_19_203700_create_product_quanitity_prices_table',1),(42,'2025_11_21_092629_create_supplier_wallet_transactions_table',1),(43,'2025_11_21_093024_create_product_images_table',1),(44,'2025_12_01_094351_add_wallet_balance_to_supplliers_table',1),(45,'2025_12_01_125934_cashier_cash_drops',1),(46,'2025_12_01_192738_add_show_in_cashier_cash_drop_to_finance_accounts_table',1),(47,'2025_12_02_082403_add_discount_columns_to_sales_order_details',1),(48,'2025_12_02_082452_add_discount_columns_to_purchase_order_details',1),(49,'2025_12_02_094902_add_extra_columns_to_stock_movements_table',1),(50,'2025_12_02_165331_change_document_date_to_datetime_in_stock_movements',1),(51,'2025_12_03_141341_create_customer_ledgers_table',1),(52,'2025_12_03_141429_create_supplier_ledgers_table',1),(53,'2025_12_22_065842_create_user_settings_table',1),(54,'2025_12_22_070151_create_customer_settings_table',1),(55,'2025_12_22_070411_create_user_notifications_table',1),(56,'2025_12_22_070519_create_customer_notifications_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operational_cost_categories`
--

DROP TABLE IF EXISTS `operational_cost_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `operational_cost_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `operational_cost_categories_name_unique` (`name`),
  KEY `operational_cost_categories_created_by_foreign` (`created_by`),
  KEY `operational_cost_categories_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `operational_cost_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `operational_cost_categories_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operational_cost_categories`
--

LOCK TABLES `operational_cost_categories` WRITE;
/*!40000 ALTER TABLE `operational_cost_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `operational_cost_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operational_costs`
--

DROP TABLE IF EXISTS `operational_costs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `operational_costs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned DEFAULT NULL,
  `finance_account_id` bigint unsigned DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `amount` decimal(8,0) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `operational_costs_code_unique` (`code`),
  KEY `operational_costs_category_id_foreign` (`category_id`),
  KEY `operational_costs_finance_account_id_foreign` (`finance_account_id`),
  KEY `operational_costs_created_by_foreign` (`created_by`),
  KEY `operational_costs_deleted_by_foreign` (`deleted_by`),
  KEY `operational_costs_date_index` (`date`),
  KEY `operational_costs_description_index` (`description`),
  CONSTRAINT `operational_costs_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `operational_cost_categories` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `operational_costs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `operational_costs_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `operational_costs_finance_account_id_foreign` FOREIGN KEY (`finance_account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operational_costs`
--

LOCK TABLES `operational_costs` WRITE;
/*!40000 ALTER TABLE `operational_costs` DISABLE KEYS */;
/*!40000 ALTER TABLE `operational_costs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_categories`
--

DROP TABLE IF EXISTS `product_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_categories_name_unique` (`name`),
  KEY `product_categories_created_by_foreign` (`created_by`),
  KEY `product_categories_updated_by_foreign` (`updated_by`),
  KEY `product_categories_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `product_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_categories_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_categories_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_categories`
--

LOCK TABLES `product_categories` WRITE;
/*!40000 ALTER TABLE `product_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_images` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caption` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_featured_unique` (`product_id`,`is_featured`),
  KEY `product_images_created_by_foreign` (`created_by`),
  KEY `product_images_updated_by_foreign` (`updated_by`),
  KEY `product_images_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `product_images_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_images_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_images_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_images`
--

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_quantity_prices`
--

DROP TABLE IF EXISTS `product_quantity_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_quantity_prices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_unit_id` bigint unsigned NOT NULL,
  `price_type` enum('price_1','price_2','price_3') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipe harga yang dimodifikasi.',
  `min_quantity` decimal(10,3) NOT NULL DEFAULT '1.000' COMMENT 'Kuantitas minimum untuk rentang harga ini.',
  `price` decimal(10,2) NOT NULL COMMENT 'Harga satuan yang berlaku.',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unit_price_qty_unique` (`product_unit_id`,`price_type`,`min_quantity`),
  CONSTRAINT `product_quantity_prices_product_unit_id_foreign` FOREIGN KEY (`product_unit_id`) REFERENCES `product_units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_quantity_prices`
--

LOCK TABLES `product_quantity_prices` WRITE;
/*!40000 ALTER TABLE `product_quantity_prices` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_quantity_prices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_units`
--

DROP TABLE IF EXISTS `product_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_units` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
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
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_units_product_id_name_unique` (`product_id`,`name`),
  UNIQUE KEY `product_units_barcode_unique` (`barcode`),
  KEY `product_units_created_by_foreign` (`created_by`),
  KEY `product_units_updated_by_foreign` (`updated_by`),
  KEY `product_units_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `product_units_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_units_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_units_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_units_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_units`
--

LOCK TABLES `product_units` WRITE;
/*!40000 ALTER TABLE `product_units` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned DEFAULT NULL,
  `supplier_id` bigint unsigned DEFAULT NULL,
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
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_name_unique` (`name`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_supplier_id_foreign` (`supplier_id`),
  KEY `products_created_by_foreign` (`created_by`),
  KEY `products_updated_by_foreign` (`updated_by`),
  KEY `products_deleted_by_foreign` (`deleted_by`),
  KEY `products_barcode_index` (`barcode`),
  KEY `products_type_index` (`type`),
  KEY `products_active_index` (`active`),
  KEY `products_stock_index` (`stock`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `products_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `products_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_details`
--

DROP TABLE IF EXISTS `purchase_order_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_order_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned DEFAULT NULL,
  `return_id` bigint unsigned DEFAULT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
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
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_order_details_order_id_foreign` (`order_id`),
  KEY `purchase_order_details_return_id_foreign` (`return_id`),
  KEY `purchase_order_details_product_id_foreign` (`product_id`),
  KEY `purchase_order_details_created_by_foreign` (`created_by`),
  KEY `purchase_order_details_updated_by_foreign` (`updated_by`),
  KEY `purchase_order_details_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `purchase_order_details_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_order_details_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_order_details_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_order_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `purchase_order_details_return_id_foreign` FOREIGN KEY (`return_id`) REFERENCES `purchase_order_returns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_order_details_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_details`
--

LOCK TABLES `purchase_order_details` WRITE;
/*!40000 ALTER TABLE `purchase_order_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_order_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_payments`
--

DROP TABLE IF EXISTS `purchase_order_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_order_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned DEFAULT NULL,
  `return_id` bigint unsigned DEFAULT NULL,
  `supplier_id` bigint unsigned DEFAULT NULL,
  `finance_account_id` bigint unsigned DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(18,2) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_order_payments_code_unique` (`code`),
  KEY `purchase_order_payments_order_id_foreign` (`order_id`),
  KEY `purchase_order_payments_return_id_foreign` (`return_id`),
  KEY `purchase_order_payments_supplier_id_foreign` (`supplier_id`),
  KEY `purchase_order_payments_finance_account_id_foreign` (`finance_account_id`),
  KEY `purchase_order_payments_created_by_foreign` (`created_by`),
  KEY `purchase_order_payments_deleted_by_foreign` (`deleted_by`),
  KEY `purchase_order_payments_type_index` (`type`),
  KEY `purchase_order_payments_amount_index` (`amount`),
  CONSTRAINT `purchase_order_payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_order_payments_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_order_payments_finance_account_id_foreign` FOREIGN KEY (`finance_account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `purchase_order_payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_order_payments_return_id_foreign` FOREIGN KEY (`return_id`) REFERENCES `purchase_order_returns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_order_payments_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_payments`
--

LOCK TABLES `purchase_order_payments` WRITE;
/*!40000 ALTER TABLE `purchase_order_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_order_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_returns`
--

DROP TABLE IF EXISTS `purchase_order_returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_order_returns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint unsigned DEFAULT NULL,
  `cashier_id` bigint unsigned DEFAULT NULL,
  `supplier_id` bigint unsigned DEFAULT NULL,
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
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_order_returns_code_unique` (`code`),
  KEY `purchase_order_returns_purchase_order_id_foreign` (`purchase_order_id`),
  KEY `purchase_order_returns_cashier_id_foreign` (`cashier_id`),
  KEY `purchase_order_returns_supplier_id_foreign` (`supplier_id`),
  KEY `purchase_order_returns_created_by_foreign` (`created_by`),
  KEY `purchase_order_returns_updated_by_foreign` (`updated_by`),
  KEY `purchase_order_returns_deleted_by_foreign` (`deleted_by`),
  KEY `purchase_order_returns_status_index` (`status`),
  KEY `purchase_order_returns_refund_status_index` (`refund_status`),
  KEY `purchase_order_returns_datetime_index` (`datetime`),
  KEY `purchase_order_returns_remaining_refund_index` (`remaining_refund`),
  CONSTRAINT `purchase_order_returns_cashier_id_foreign` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `purchase_order_returns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_order_returns_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_order_returns_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `purchase_order_returns_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `purchase_order_returns_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_returns`
--

LOCK TABLES `purchase_order_returns` WRITE;
/*!40000 ALTER TABLE `purchase_order_returns` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_order_returns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_orders`
--

DROP TABLE IF EXISTS `purchase_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint unsigned DEFAULT NULL,
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
  `created_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_orders_code_unique` (`code`),
  KEY `1` (`supplier_id`),
  KEY `purchase_orders_created_by_foreign` (`created_by`),
  KEY `purchase_orders_deleted_by_foreign` (`deleted_by`),
  KEY `purchase_orders_type_index` (`type`),
  KEY `purchase_orders_status_index` (`status`),
  KEY `purchase_orders_payment_status_index` (`payment_status`),
  KEY `purchase_orders_delivery_status_index` (`delivery_status`),
  KEY `purchase_orders_datetime_index` (`datetime`),
  KEY `purchase_orders_due_date_index` (`due_date`),
  CONSTRAINT `1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `purchase_orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_orders_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_orders`
--

LOCK TABLES `purchase_orders` WRITE;
/*!40000 ALTER TABLE `purchase_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_order_details`
--

DROP TABLE IF EXISTS `sales_order_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sales_order_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned DEFAULT NULL,
  `return_id` bigint unsigned DEFAULT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
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
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sales_order_details_order_id_foreign` (`order_id`),
  KEY `sales_order_details_return_id_foreign` (`return_id`),
  KEY `sales_order_details_product_id_foreign` (`product_id`),
  KEY `sales_order_details_created_by_foreign` (`created_by`),
  KEY `sales_order_details_updated_by_foreign` (`updated_by`),
  KEY `sales_order_details_deleted_by_foreign` (`deleted_by`),
  KEY `sales_order_details_product_barcode_index` (`product_barcode`),
  CONSTRAINT `sales_order_details_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_order_details_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_order_details_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `sales_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_order_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `sales_order_details_return_id_foreign` FOREIGN KEY (`return_id`) REFERENCES `sales_order_returns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_order_details_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_order_details`
--

LOCK TABLES `sales_order_details` WRITE;
/*!40000 ALTER TABLE `sales_order_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `sales_order_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_order_payments`
--

DROP TABLE IF EXISTS `sales_order_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sales_order_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned DEFAULT NULL,
  `return_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `finance_account_id` bigint unsigned DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(18,2) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_order_payments_code_unique` (`code`),
  KEY `sales_order_payments_order_id_foreign` (`order_id`),
  KEY `sales_order_payments_return_id_foreign` (`return_id`),
  KEY `sales_order_payments_customer_id_foreign` (`customer_id`),
  KEY `sales_order_payments_finance_account_id_foreign` (`finance_account_id`),
  KEY `sales_order_payments_created_by_foreign` (`created_by`),
  KEY `sales_order_payments_deleted_by_foreign` (`deleted_by`),
  KEY `sales_order_payments_type_index` (`type`),
  CONSTRAINT `sales_order_payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_order_payments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `sales_order_payments_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_order_payments_finance_account_id_foreign` FOREIGN KEY (`finance_account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `sales_order_payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `sales_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_order_payments_return_id_foreign` FOREIGN KEY (`return_id`) REFERENCES `sales_order_returns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_order_payments`
--

LOCK TABLES `sales_order_payments` WRITE;
/*!40000 ALTER TABLE `sales_order_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `sales_order_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_order_returns`
--

DROP TABLE IF EXISTS `sales_order_returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sales_order_returns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sales_order_id` bigint unsigned DEFAULT NULL,
  `cashier_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
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
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_order_returns_code_unique` (`code`),
  KEY `sales_order_returns_sales_order_id_foreign` (`sales_order_id`),
  KEY `sales_order_returns_cashier_id_foreign` (`cashier_id`),
  KEY `sales_order_returns_customer_id_foreign` (`customer_id`),
  KEY `sales_order_returns_created_by_foreign` (`created_by`),
  KEY `sales_order_returns_updated_by_foreign` (`updated_by`),
  KEY `sales_order_returns_deleted_by_foreign` (`deleted_by`),
  KEY `sales_order_returns_status_index` (`status`),
  KEY `sales_order_returns_refund_status_index` (`refund_status`),
  KEY `sales_order_returns_datetime_index` (`datetime`),
  KEY `sales_order_returns_remaining_refund_index` (`remaining_refund`),
  CONSTRAINT `sales_order_returns_cashier_id_foreign` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `sales_order_returns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_order_returns_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `sales_order_returns_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_order_returns_sales_order_id_foreign` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `sales_order_returns_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_order_returns`
--

LOCK TABLES `sales_order_returns` WRITE;
/*!40000 ALTER TABLE `sales_order_returns` DISABLE KEYS */;
/*!40000 ALTER TABLE `sales_order_returns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_orders`
--

DROP TABLE IF EXISTS `sales_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sales_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned DEFAULT NULL,
  `cashier_id` bigint unsigned DEFAULT NULL,
  `cashier_session_id` bigint unsigned DEFAULT NULL,
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
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_orders_code_unique` (`code`),
  KEY `sales_orders_customer_id_foreign` (`customer_id`),
  KEY `sales_orders_cashier_id_foreign` (`cashier_id`),
  KEY `sales_orders_cashier_session_id_foreign` (`cashier_session_id`),
  KEY `sales_orders_created_by_foreign` (`created_by`),
  KEY `sales_orders_updated_by_foreign` (`updated_by`),
  KEY `sales_orders_deleted_by_foreign` (`deleted_by`),
  KEY `sales_orders_type_index` (`type`),
  KEY `sales_orders_status_index` (`status`),
  KEY `sales_orders_payment_status_index` (`payment_status`),
  KEY `sales_orders_delivery_status_index` (`delivery_status`),
  KEY `sales_orders_datetime_index` (`datetime`),
  KEY `sales_orders_due_date_index` (`due_date`),
  CONSTRAINT `sales_orders_cashier_id_foreign` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `sales_orders_cashier_session_id_foreign` FOREIGN KEY (`cashier_session_id`) REFERENCES `cashier_sessions` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `sales_orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `sales_orders_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_orders_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_orders`
--

LOCK TABLES `sales_orders` WRITE;
/*!40000 ALTER TABLE `sales_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `sales_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`key`),
  KEY `settings_created_by_foreign` (`created_by`),
  KEY `settings_updated_by_foreign` (`updated_by`),
  CONSTRAINT `settings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `settings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_adjustment_details`
--

DROP TABLE IF EXISTS `stock_adjustment_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_adjustment_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint unsigned DEFAULT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
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
  `notes` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `stock_adjustment_details_parent_id_foreign` (`parent_id`),
  KEY `stock_adjustment_details_product_id_foreign` (`product_id`),
  CONSTRAINT `stock_adjustment_details_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `stock_adjustments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_adjustment_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_adjustment_details`
--

LOCK TABLES `stock_adjustment_details` WRITE;
/*!40000 ALTER TABLE `stock_adjustment_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_adjustment_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_adjustments`
--

DROP TABLE IF EXISTS `stock_adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_adjustments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
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
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_adjustments_code_unique` (`code`),
  KEY `stock_adjustments_created_by_foreign` (`created_by`),
  KEY `stock_adjustments_updated_by_foreign` (`updated_by`),
  KEY `stock_adjustments_deleted_by_foreign` (`deleted_by`),
  KEY `stock_adjustments_datetime_index` (`datetime`),
  KEY `stock_adjustments_status_index` (`status`),
  KEY `stock_adjustments_type_index` (`type`),
  CONSTRAINT `stock_adjustments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_adjustments_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_adjustments_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_adjustments`
--

LOCK TABLES `stock_adjustments` WRITE;
/*!40000 ALTER TABLE `stock_adjustments` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_adjustments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned DEFAULT NULL,
  `ref_id` bigint unsigned DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ref_type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `parent_id` bigint unsigned DEFAULT NULL COMMENT 'ID dokumen induk, misalnya SO/PO/SA',
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
  `created_by` bigint unsigned DEFAULT NULL,
  `party_id` bigint unsigned DEFAULT NULL COMMENT 'ID customer atau supplier',
  `party_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Jenis party: customer / supplier',
  `party_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `party_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nama customer/supplier (snapshot)',
  `document_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nomor dokumen induk (misalnya SO-2025-0012)',
  `document_datetime` datetime DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_movements_code_unique` (`code`),
  KEY `stock_movements_product_id_foreign` (`product_id`),
  KEY `stock_movements_ref_type_ref_id_index` (`ref_type`,`ref_id`),
  KEY `stock_movements_created_by_foreign` (`created_by`),
  KEY `stock_movements_deleted_by_foreign` (`deleted_by`),
  KEY `stock_movements_parent_id_index` (`parent_id`),
  KEY `stock_movements_parent_ref_type_index` (`parent_ref_type`),
  KEY `stock_movements_party_id_index` (`party_id`),
  KEY `stock_movements_party_type_index` (`party_type`),
  KEY `stock_movements_document_code_index` (`document_code`),
  KEY `stock_movements_party_code_index` (`party_code`),
  CONSTRAINT `stock_movements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_movements_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_ledgers`
--

DROP TABLE IF EXISTS `supplier_ledgers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `supplier_ledgers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint unsigned NOT NULL,
  `finance_account_id` bigint unsigned DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_id` bigint unsigned DEFAULT NULL,
  `datetime` datetime NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `running_balance` decimal(12,2) DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `supplier_ledgers_code_unique` (`code`),
  KEY `supplier_ledgers_supplier_id_foreign` (`supplier_id`),
  KEY `supplier_ledgers_finance_account_id_foreign` (`finance_account_id`),
  KEY `supplier_ledgers_ref_type_ref_id_index` (`ref_type`,`ref_id`),
  KEY `supplier_ledgers_created_by_foreign` (`created_by`),
  KEY `supplier_ledgers_updated_by_foreign` (`updated_by`),
  KEY `supplier_ledgers_deleted_by_foreign` (`deleted_by`),
  KEY `supplier_ledgers_datetime_index` (`datetime`),
  KEY `supplier_ledgers_type_index` (`type`),
  CONSTRAINT `supplier_ledgers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `supplier_ledgers_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `supplier_ledgers_finance_account_id_foreign` FOREIGN KEY (`finance_account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `supplier_ledgers_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `supplier_ledgers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_ledgers`
--

LOCK TABLES `supplier_ledgers` WRITE;
/*!40000 ALTER TABLE `supplier_ledgers` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_ledgers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_wallet_transactions`
--

DROP TABLE IF EXISTS `supplier_wallet_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `supplier_wallet_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `finance_account_id` bigint unsigned DEFAULT NULL,
  `supplier_id` bigint unsigned DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ref_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_id` bigint unsigned DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `supplier_wallet_transactions_code_unique` (`code`),
  KEY `supplier_wallet_transactions_finance_account_id_foreign` (`finance_account_id`),
  KEY `supplier_wallet_transactions_supplier_id_foreign` (`supplier_id`),
  KEY `supplier_wallet_transactions_ref_type_ref_id_index` (`ref_type`,`ref_id`),
  KEY `supplier_wallet_transactions_created_by_foreign` (`created_by`),
  KEY `supplier_wallet_transactions_updated_by_foreign` (`updated_by`),
  KEY `supplier_wallet_transactions_deleted_by_foreign` (`deleted_by`),
  KEY `supplier_wallet_transactions_datetime_index` (`datetime`),
  KEY `supplier_wallet_transactions_type_index` (`type`),
  CONSTRAINT `supplier_wallet_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `supplier_wallet_transactions_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `supplier_wallet_transactions_finance_account_id_foreign` FOREIGN KEY (`finance_account_id`) REFERENCES `finance_accounts` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `supplier_wallet_transactions_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `supplier_wallet_transactions_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_wallet_transactions`
--

LOCK TABLES `supplier_wallet_transactions` WRITE;
/*!40000 ALTER TABLE `supplier_wallet_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_wallet_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suppliers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
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
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `suppliers_code_unique` (`code`),
  KEY `suppliers_created_by_foreign` (`created_by`),
  KEY `suppliers_updated_by_foreign` (`updated_by`),
  KEY `suppliers_deleted_by_foreign` (`deleted_by`),
  KEY `suppliers_name_index` (`name`),
  KEY `suppliers_phone_1_index` (`phone_1`),
  KEY `suppliers_phone_2_index` (`phone_2`),
  KEY `suppliers_phone_3_index` (`phone_3`),
  KEY `suppliers_active_index` (`active`),
  KEY `suppliers_balance_index` (`balance`),
  KEY `suppliers_wallet_balance_index` (`wallet_balance`),
  CONSTRAINT `suppliers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `suppliers_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `suppliers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tax_schemes`
--

DROP TABLE IF EXISTS `tax_schemes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tax_schemes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nama skema pajak (misal: PPN 11%)',
  `rate_percentage` decimal(5,2) NOT NULL COMMENT 'Tarif pajak (misal: 11.00)',
  `is_inclusive` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'TRUE jika harga sudah termasuk pajak (Inclusive)',
  `tax_authority` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Otoritas penerima pajak',
  `description` text COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tax_schemes_name_unique` (`name`),
  KEY `tax_schemes_created_by_foreign` (`created_by`),
  KEY `tax_schemes_updated_by_foreign` (`updated_by`),
  KEY `tax_schemes_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `tax_schemes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tax_schemes_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tax_schemes_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tax_schemes`
--

LOCK TABLES `tax_schemes` WRITE;
/*!40000 ALTER TABLE `tax_schemes` DISABLE KEYS */;
/*!40000 ALTER TABLE `tax_schemes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `uoms`
--

DROP TABLE IF EXISTS `uoms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `uoms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uoms_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uoms`
--

LOCK TABLES `uoms` WRITE;
/*!40000 ALTER TABLE `uoms` DISABLE KEYS */;
/*!40000 ALTER TABLE `uoms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_activity_logs`
--

DROP TABLE IF EXISTS `user_activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_activity_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logged_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activity_category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activity_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_activity_logs_user_id_index` (`user_id`),
  KEY `user_activity_logs_username_index` (`username`),
  KEY `user_activity_logs_logged_at_index` (`logged_at`),
  KEY `user_activity_logs_activity_category_index` (`activity_category`),
  KEY `user_activity_logs_activity_name_index` (`activity_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_activity_logs`
--

LOCK TABLES `user_activity_logs` WRITE;
/*!40000 ALTER TABLE `user_activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_notifications`
--

DROP TABLE IF EXISTS `user_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_notifications_user_id_read_at_index` (`user_id`,`read_at`),
  CONSTRAINT `user_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_notifications`
--

LOCK TABLES `user_notifications` WRITE;
/*!40000 ALTER TABLE `user_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_settings`
--

DROP TABLE IF EXISTS `user_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_settings` (
  `user_id` bigint unsigned NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`,`key`),
  CONSTRAINT `user_settings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_settings`
--

LOCK TABLES `user_settings` WRITE;
/*!40000 ALTER TABLE `user_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
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
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_name_index` (`name`),
  KEY `users_type_index` (`type`),
  KEY `users_active_index` (`active`),
  KEY `users_last_login_datetime_index` (`last_login_datetime`),
  KEY `users_last_activity_description_index` (`last_activity_description`),
  KEY `users_last_activity_datetime_index` (`last_activity_datetime`),
  KEY `users_created_by_foreign` (`created_by`),
  KEY `users_updated_by_foreign` (`updated_by`),
  KEY `users_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `users_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','Administrator','$2y$12$CeLrslfFyC6xeXR7R9eifOztevfeHC.YQEZqmld9NLgQJTsexmIWS','super_user',1,NULL,'',NULL,'05ZbzOsdHK','2025-12-23 12:46:31',NULL,NULL,NULL,NULL,NULL),(2,'kasir1','Kasir 1','$2y$12$CeLrslfFyC6xeXR7R9eifOztevfeHC.YQEZqmld9NLgQJTsexmIWS','standard_user',1,NULL,'',NULL,'yzc9YyfNqP','2025-12-23 12:46:31',NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-23 12:46:33
