<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;
use Modules\Admin\Features\UserActivityLog\Formatters\MetaDataFormatterFactory;

class UserActivityLog extends Model
{
    public $timestamps = false;

    protected $table = 'user_activity_logs';

    /**
     * Konstanta Kategori Aktivitas
     */
    public const Category_Auth = 'auth';
    public const Category_Inventory = 'inventory';
    public const Category_Settings = 'settings';
    public const Category_UserProfile = 'user-profile';
    public const Category_User = 'user';
    public const Category_UserRole = 'user-role';
    public const Category_Product = 'product';
    public const Category_ProductCategory = 'product-category';
    public const Category_Supplier = 'supplier';
    public const Category_Customer = 'customer';
    public const Category_UserActivityLog = 'user-activity-log';
    public const Category_Finance = 'finance';
    public const Category_SalesOrder = 'sales-order';
    public const Category_PurchaseOrder = 'purchase-order';
    public const Category_OperationalCost = 'operational-cost';
    public const Category_OperationalCostCategory = 'operational-cost-category';
    public const Category_Cashier = 'cashier';
    public const Category_CustomerWallet = 'customer-wallet';

    /**
     * Array pemetaan Kategori untuk tampilan (Opsional, tapi konsisten)
     */
    public const Categories = [
        self::Category_Auth => 'Autentikasi Pengguna',
        self::Category_Inventory => 'Manajemen Inventori',
        self::Category_Finance => 'Transaksi & Keuangan',
        self::Category_Settings => 'Pengaturan Sistem',
        self::Category_UserProfile => 'Profil Pengguna',
        self::Category_User => 'Manajemen Pengguna',
        self::Category_UserRole => 'Manajemen Peran Pengguna',
        self::Category_Product => 'Manajemen Produk',
        self::Category_ProductCategory => 'Manajemen Kategori Produk',
        self::Category_Supplier => 'Manajemen Pemasok',
        self::Category_Customer => 'Manajemen Pelanggan',
        self::Category_UserActivityLog => 'Manajemen Log Aktifitas Pengguna',
        self::Category_SalesOrder => 'Manajemen Order Penjualan',
        self::Category_PurchaseOrder => 'Manajemen Order Pembelian',
        self::Category_OperationalCost => 'Manajemen Biaya Operasional',
        self::Category_OperationalCostCategory => 'Manajemen Kategori Biaya Operasional',
        self::Category_Cashier => 'Manajemen Terminal dan Sesi Kasir',
        self::Category_CustomerWallet => 'Manajemen Walet Pelanggan',
    ];

    /**
     * Konstanta Nama/Jenis Aktivitas
     */

    // Authentication
    public const Name_Auth_Login = 'auth.login';
    public const Name_Auth_Logout = 'auth.logout';

    // Settings: User, user profile, user role
    public const Name_User_Create = 'user.create';
    public const Name_User_Update = 'user.update';
    public const Name_User_Delete = 'user.delete';
    public const Name_UserProfile_UpdateProfile  = 'user-profile.update';
    public const Name_UserProfile_ChangePassword = 'user-profile.change-password';
    public const Name_UserRole_Create = 'user-role.create';
    public const Name_UserRole_Update = 'user-role.update';
    public const Name_UserRole_Delete = 'user-role.delete';
    public const Name_UserActivityLog_Clear = 'user-activity-log.clear';
    public const Name_UpdatePosSettings = 'pos-settings.update';
    public const Name_UpdateCompanyProfile = 'company-profile.update';

    // Product managment
    public const Name_Product_Create = 'product.create';
    public const Name_Product_Update = 'product.update';
    public const Name_Product_Delete = 'product.delete';
    public const Name_Product_Import = 'product.import';
    public const Name_Product_Export = 'product.export';
    public const Name_ProductCategory_Create = 'product-category.create';
    public const Name_ProductCategory_Update = 'product-category.update';
    public const Name_ProductCategory_Delete = 'product-category.delete';

    // Purchase Order
    public const Name_Supplier_Create = 'supplier.create';
    public const Name_Supplier_Update = 'supplier.update';
    public const Name_Supplier_Delete = 'supplier.delete';
    public const Name_Supplier_Import = 'supplier.import';
    public const Name_PurchaseOrder_Create = 'purchase-order.create';
    public const Name_PurchaseOrder_Close  = 'purchase-order.close';
    public const Name_PurchaseOrder_Delete = 'purchase-order.delete';
    public const Name_PurchaseOrder_Cancel = 'purchase-order.cancel';
    public const Name_PurchaseOrder_AddPayment    = 'sales-purchase.add-payment';
    public const Name_PurchaseOrder_DeletePayment = 'sales-purchase.delete-payment';

    // Sales Order
    public const Name_Customer_Create = 'customer.create';
    public const Name_Customer_Update = 'customer.update';
    public const Name_Customer_Delete = 'customer.delete';
    public const Name_Customer_Import = 'customer.import';
    public const Name_SalesOrder_Create = 'sales-order.create';
    public const Name_SalesOrder_Close  = 'sales-order.close';
    public const Name_SalesOrder_Delete = 'sales-order.delete';
    public const Name_SalesOrder_Cancel = 'sales-order.cancel';
    public const Name_SalesOrder_AddPayment    = 'sales-order.add-payment';
    public const Name_SalesOrder_DeletePayment = 'sales-order.delete-payment';

    // Cashier
    public const Name_CashierTerminal_Create = 'cashier-terminal.create';
    public const Name_CashierTerminal_Update = 'cashier-terminal.update';
    public const Name_CashierTerminal_Delete = 'cashier-terminal.delete';

    public const Name_CashierSession_Open   = 'cashier-session.open';
    public const Name_CashierSession_Close  = 'cashier-session.close';
    public const Name_CashierSession_Delete = 'cashier-session.delete';

    // Finance
    public const Name_FinanceAccount_Create = 'finance-account.create';
    public const Name_FinanceAccount_Update = 'finance-account.update';
    public const Name_FinanceAccount_Delete = 'finance-account.delete';

    public const Name_FinanceTransaction_Create = 'finance-transaction.create';
    public const Name_FinanceTransaction_Update = 'finance-transaction.update';
    public const Name_FinanceTransaction_Delete = 'finance-transaction.delete';

    // Customer Wallet
    public const Name_CustomerWalletTopupConfirmation_Approve = 'customer-wallet-topup-confirmation.approve';
    public const Name_CustomerWalletTopupConfirmation_Reject  = 'customer-wallet-topup-confirmation.reject';
    public const Name_CustomerWalletTopupConfirmation_Delete  = 'customer-wallet-topup-confirmation.delete';

    public const Name_CustomerWalletTransaction_Create = 'customer-wallet-transaction.create';
    public const Name_CustomerWalletTransaction_Update = 'customer-wallet-transaction.update';
    public const Name_CustomerWalletTransaction_Delete = 'customer-wallet-transaction.delete';

    // Operational Cost
    public const Name_OperationalCost_Create = 'operational-cost.create';
    public const Name_OperationalCost_Update = 'operational-cost.update';
    public const Name_OperationalCost_Delete = 'operational-cost.delete';

    public const Name_OperationalCostCategory_Create = 'operational-cost-category.create';
    public const Name_OperationalCostCategory_Update = 'operational-cost-category.update';
    public const Name_OperationalCostCategory_Delete = 'operational-cost-category.delete';

    /**
     * Array pemetaan Nama Aktivitas
     */
    public const Names = [
        self::Name_Auth_Login     => 'Login',
        self::Name_Auth_Logout    => 'Logout',

        self::Name_UserProfile_UpdateProfile => 'Memperbarui Profile',
        self::Name_UserProfile_ChangePassword => 'Mengganti Kata Sandi',
        self::Name_User_Create => 'Menambah Pengguna',
        self::Name_User_Update => 'Memperbarui Pengguna',
        self::Name_User_Delete => 'Menghapus Pengguna',
        self::Name_UserActivityLog_Clear => 'Membersihkan Log Aktifitas Pengguna',
        self::Name_UpdatePosSettings => 'Memperbarui Pengaturan POS',
        self::Name_UpdateCompanyProfile => 'Memperbarui Profil Perusahaan',
        self::Name_UserRole_Create => 'Membuat Peran Pengguna',
        self::Name_UserRole_Update => 'Memperbarui Peran Pengguna',
        self::Name_UserRole_Delete => 'Menghapus Peran Pengguna',

        // Inventory
        self::Name_Product_Create => 'Membuat Produk',
        self::Name_Product_Update => 'Memperbarui Produk',
        self::Name_Product_Delete => 'Menghapus Produk',
        self::Name_Product_Import => 'Mengimpor Produk',
        self::Name_Product_Export => 'Mengekspor Produk',
        self::Name_ProductCategory_Create => 'Membuat Kategori Produk',
        self::Name_ProductCategory_Update => 'Memperbarui Kategori Produk',
        self::Name_ProductCategory_Delete => 'Menghapus Kategori Produk',

        // Purchase Order
        self::Name_Supplier_Create => 'Membuat Pemasok',
        self::Name_Supplier_Update => 'Memperbarui Pemasok',
        self::Name_Supplier_Delete => 'Menghapus Pemasok',
        self::Name_Supplier_Import => 'Mengimpor Pemasok',
        self::Name_PurchaseOrder_Create => 'Membuat Order Pembelian',
        self::Name_PurchaseOrder_Close  => 'Menutup Order Pembelian',
        self::Name_PurchaseOrder_Delete => 'Menghapus Order Pembelian',
        self::Name_PurchaseOrder_Cancel => 'Membatalkan Order Pembelian',
        self::Name_PurchaseOrder_AddPayment    => 'Menamabah Pembayaran Order Pembelian',
        self::Name_PurchaseOrder_DeletePayment => 'Menghapus Pembayaran Order Pembelian',

        // Sales Order
        self::Name_Customer_Create => 'Membuat Pelanggan',
        self::Name_Customer_Update => 'Memperbarui Pelanggan',
        self::Name_Customer_Delete => 'Menghapus Pelanggan',
        self::Name_Customer_Import => 'Mengimpor Pelanggan',
        self::Name_SalesOrder_Create => 'Membuat Order Penjualan',
        self::Name_SalesOrder_Close  => 'Menutup Order Penjualan',
        self::Name_SalesOrder_Delete => 'Menghapus Order Penjualan',
        self::Name_SalesOrder_Cancel => 'Membatalkan Order Penjualan',
        self::Name_SalesOrder_AddPayment    => 'Menamabah Pembayaran Order Penjualan',
        self::Name_SalesOrder_DeletePayment => 'Menghapus Pembayaran Order Penjualan',

        self::Name_OperationalCost_Create => 'Menambah Biaya Operasional',
        self::Name_OperationalCost_Update => 'Memperbarui Biaya Operasional',
        self::Name_OperationalCost_Delete => 'Menghapus Biaya Operasional',
        self::Name_OperationalCostCategory_Create => 'Menambah Kategori Biaya Operasional',
        self::Name_OperationalCostCategory_Update => 'Memperbarui Kategori Biaya Operasional',
        self::Name_OperationalCostCategory_Delete => 'Menghapus Kategori Biaya Operasional',

    ];

    // Kolom yang dapat diisi massal
    protected $fillable = [
        'user_id',
        'username',
        'logged_at',
        'activity_category',
        'activity_name',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    /**
     * Label agar bisa dikirim ke client sehingga tidak perlu mapping di client.
     */
    protected $appends = [
        'activity_name_label',
        'activity_category_label',
    ];

    /**
     * Casts untuk konversi tipe data otomatis.
     */
    protected $casts = [
        // Konversi kolom 'metadata' ke array/objek PHP (penting untuk JSON)
        'metadata' => 'array',
        // Pastikan kolom waktu diproses sebagai instance Carbon
        'logged_at' => 'datetime',
    ];

    /**
     * Accessor untuk mendapatkan Label Tampilan dari Kunci Nama Aktivitas.
     */
    public function getActivityNameLabelAttribute(): string
    {
        return self::Names[$this->activity_name] ?? $this->activity_name;
    }

    /**
     * Accessor untuk mendapatkan Label Tampilan dari Kunci Kategori Aktifitas.
     */
    public function getActivityCategoryLabelAttribute(): string
    {
        return self::Categories[$this->activity_category] ?? $this->activity_category;
    }

    /**
     * Relasi ke model User.
     * Log bisa nullable jika pengguna dihapus (onDelete('set null')).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Menganalisis User-Agent untuk mendapatkan nama Browser dan Versi.
     * Ini hanya dieksekusi saat mengakses $log->browser.
     */
    public function getBrowserAttribute(): string
    {
        if (empty($this->user_agent)) {
            return 'Tidak Diketahui';
        }

        // SIMULASI LOGIKA PARSING
        // $agent = new Agent();
        // $agent->setUserAgent($this->user_agent);
        // return $agent->browser() . ' ' . $agent->version($agent->browser());

        if (str_contains($this->user_agent, 'Chrome')) {
            return 'Google Chrome (Parsed)';
        }
        if (str_contains($this->user_agent, 'Firefox')) {
            return 'Mozilla Firefox (Parsed)';
        }
        // ... Logika lengkap
        return 'Browser Lain';
    }

    /**
     * Menganalisis User-Agent untuk mendapatkan Sistem Operasi.
     * Ini hanya dieksekusi saat mengakses $log->os.
     */
    public function getOsAttribute(): string
    {
        if (empty($this->user_agent)) {
            return 'Tidak Diketahui';
        }

        // --- SIMULASI LOGIKA PARSING ---
        // $agent->setUserAgent($this->user_agent);
        // return $agent->platform() . ' ' . $agent->version($agent->platform());

        if (str_contains($this->user_agent, 'Windows NT 10.0')) {
            return 'Windows 10/11 (Parsed)';
        }
        if (str_contains($this->user_agent, 'Mac OS X')) {
            return 'macOS (Parsed)';
        }
        // ... Logika lengkap
        return 'OS Lain';
    }

    /**
     * Melakukan lookup GeoIP untuk mendapatkan nama Negara.
     * Ini hanya dieksekusi saat mengakses $log->country.
     */
    public function getCountryAttribute(): string
    {
        if (empty($this->ip_address)) {
            return 'IP Tidak Tersedia';
        }

        // --- SIMULASI LOGIKA GEOIP
        // $ipService = resolve(GeoIpService::class);
        // return $ipService->getCountryName($this->ip_address);

        if (str_starts_with($this->ip_address, '103.')) {
            return 'Indonesia (GeoIP Lookup)';
        }

        return 'Negara Lain (GeoIP Lookup)';
    }

    protected function getFormattedMetadataAttribute(): array
    {
        $metaData = $this->metadata ?? [];
        try {
            if (!empty($metaData['formatter'])) {
                $formatter = MetaDataFormatterFactory::create($metaData['formatter']);
                return $formatter->format($metaData);
            }

            return [];
        } catch (InvalidArgumentException $e) {
            return [
                ['type' => 'plain', 'label' => 'Data Mentah (JSON)', 'value' => json_encode($metaData, JSON_PRETTY_PRINT)],
            ];
        } catch (\Throwable $e) {
            return [
                ['type' => 'simple', 'label' => 'Error Pemformatan', 'value' => $e->getMessage()],
                ['type' => 'simple', 'label' => 'Kategori Log', 'value' => $this->activity_category],
            ];
        }
    }
}
