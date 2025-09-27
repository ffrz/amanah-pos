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

    /**
     * Array pemetaan Kategori untuk tampilan (Opsional, tapi konsisten)
     */
    public const Categories = [
        self::Category_Auth => 'Autentikasi Pengguna',
        self::Category_Inventory => 'Manajemen Inventori',
        self::Category_Finance => 'Transaksi & Keuangan',
        self::Category_Settings => 'Pengaturan Sistem',
    ];

    /**
     * Konstanta Nama/Jenis Aktivitas
     */
    public const Name_Auth_Login = 'auth.login';
    public const Name_Auth_Logout = 'auth.logout';

    // Product managment
    public const Name_Product_Create = 'products.create';
    public const Name_Product_Update = 'products.update';
    public const Name_Product_Delete = 'products.delete';
    public const Name_Product_Import = 'products.import';
    public const Name_Product_Export = 'products.export';

    /**
     * Array pemetaan Nama Aktivitas
     */
    public const Names = [
        self::Name_Auth_Login     => 'Login Berhasil',
        self::Name_Auth_Logout    => 'Logout',

        self::Name_Product_Create => 'Membuat Produk',
        self::Name_Product_Update => 'Memperbarui Produk',
        self::Name_Product_Delete => 'Menghapus Produk',
        self::Name_Product_Import => 'Mengimpor Produk',
        self::Name_Product_Export => 'Mengekspor Produk',
    ];

    // Kolom yang dapat diisi massal
    protected $fillable = [
        'user_id',
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
     * Relasi ke model User.
     * Log bisa nullable jika pengguna dihapus (onDelete('set null')).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
