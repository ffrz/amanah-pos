<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivityLog extends Model
{
    public $timestamps = false;

    protected $table = 'user_activity_logs';

    /**
     * Konstanta Kategori Aktivitas
     */
    public const Category_Auth = 'auth';
    public const Category_Inventory = 'inventory';
    public const Category_Finance = 'finance';
    public const Category_Settings = 'settings';
    public const Category_UserProfile = 'user_profile';

    /**
     * Array pemetaan Kategori untuk tampilan (Opsional, tapi konsisten)
     */
    public const Categories = [
        self::Category_Auth => 'Autentikasi Pengguna',
        self::Category_Inventory => 'Manajemen Inventori',
        self::Category_Finance => 'Transaksi & Keuangan',
        self::Category_Settings => 'Pengaturan Sistem',
        self::Category_UserProfile => 'Profil Pengguna',
    ];

    /**
     * Konstanta Nama/Jenis Aktivitas
     */
    public const Name_Auth_Login = 'auth.login';
    public const Name_Auth_Logout = 'auth.logout';

    // Product managment
    public const Name_Product_Create = 'product.create';
    public const Name_Product_Update = 'product.update';
    public const Name_Product_Delete = 'product.delete';
    public const Name_Product_Import = 'product.import';
    public const Name_Product_Export = 'product.export';

    public const Name_UserRole_Create = 'user-role.create';
    public const Name_UserRole_Update = 'user-role.update';
    public const Name_UserRole_Delete = 'user-role.delete';

    public const Name_UserProfile_UpdateProfile  = 'user-profile.update';
    public const Name_UserProfile_ChangePassword = 'user-profile.change-password';

    /**
     * Array pemetaan Nama Aktivitas
     */
    public const Names = [
        self::Name_Auth_Login     => 'Login',
        self::Name_Auth_Logout    => 'Logout',

        self::Name_UserRole_Create => 'Membuat Peran Pengguna',
        self::Name_UserRole_Update => 'Memperbarui Peran Pengguna',
        self::Name_UserRole_Delete => 'Menghapus Peran Pengguna',

        self::Name_Product_Create => 'Membuat Produk',
        self::Name_Product_Update => 'Memperbarui Produk',
        self::Name_Product_Delete => 'Menghapus Produk',
        self::Name_Product_Import => 'Mengimpor Produk',
        self::Name_Product_Export => 'Mengekspor Produk',

        self::Name_UserProfile_UpdateProfile => 'Memperbarui Profile',
        self::Name_UserProfile_ChangePassword => 'Mengganti Kata Sandi',
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
     * Ini hanya dieksekusi saat Anda mengakses $log->browser.
     */
    public function getBrowserAttribute(): string
    {
        if (empty($this->user_agent)) {
            return 'Tidak Diketahui';
        }

        // --- SIMULASI LOGIKA PARSING (Ganti dengan Logic/Library Anda) ---
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
     * Ini hanya dieksekusi saat Anda mengakses $log->os.
     */
    public function getOsAttribute(): string
    {
        if (empty($this->user_agent)) {
            return 'Tidak Diketahui';
        }

        // --- SIMULASI LOGIKA PARSING (Ganti dengan Logic/Library Anda) ---
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
     * Ini hanya dieksekusi saat Anda mengakses $log->country.
     */
    public function getCountryAttribute(): string
    {
        if (empty($this->ip_address)) {
            return 'IP Tidak Tersedia';
        }

        // --- SIMULASI LOGIKA GEOIP (Ganti dengan Logic/Library GeoIP Anda) ---
        // $ipService = resolve(GeoIpService::class);
        // return $ipService->getCountryName($this->ip_address);

        if (str_starts_with($this->ip_address, '103.')) {
            return 'Indonesia (GeoIP Lookup)';
        }

        return 'Negara Lain (GeoIP Lookup)';
    }
}
