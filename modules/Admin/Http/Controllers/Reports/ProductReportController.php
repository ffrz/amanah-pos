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

namespace Modules\Admin\Http\Controllers\Reports;

use App\Models\Product;
use Illuminate\Http\Request;
use Modules\Admin\Services\CommonDataService;

class ProductReportController extends BaseController
{
    protected string $default_title = 'Laporan Daftar Produk';

    protected array $templates = [
        'report_1' => [
            'value' => 'report_1',
            'label' => 'Daftar Harga Pokok',
            'columns' => ['name', 'description', 'category', 'stock', 'cost'],
        ],
        'report_2' => [
            'value' => 'report_2',
            'label' => 'Daftar Harga Eceran',
            'columns' => ['name', 'description', 'category', 'stock', 'price_1'],
        ],
        'report_3' => [
            'value' => 'report_3',
            'label' => 'Daftar Harga Partai',
            'columns' => ['name', 'description', 'category', 'stock', 'price_2'],
        ],
        'report_4' => [
            'value' => 'report_4',
            'label' => 'Daftar Harga Grosir',
            'columns' => ['name', 'description', 'category', 'stock', 'price_3'],
        ],
        'report_5' => [
            'value' => 'report_5',
            'label' => 'Daftar Stok Aktual',
            'columns' => ['name', 'description', 'category', 'stock'],
        ],
    ];

    protected $primary_columns = [
        'name' => 'Nama',
    ];

    protected $optional_columns = [
        'description' => 'Deskripsi',
        'cost' => 'Modal / Harga Beli',
        'price_1' => 'Harga Eceran',
        'price_2' => 'Harga Partai',
        'price_3' => 'Harga Grosir',
        'stock' => 'Stok',
        'active' => 'Status',
        'type' => 'Jenis',
        'category' => 'Kategori',
        'supplier' => 'Pemasok',
    ];

    protected $initial_columns = [
        'name',
        'stock',
        'price_1',
    ];

    public function __construct(
        protected CommonDataService $commonDataService,
    ) {
        parent::__construct();
    }

    public function index()
    {
        return $this->generateIndexResponse('reports/product/Index', [

            'categories' => $this->commonDataService->getProductCategories(),
            'suppliers' => $this->commonDataService->getSuppliers(),
        ]);
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            ...$this->getDefaultValidationRules(),
            'filter.status' => 'nullable|string|in:all,active,inactive',
            'filter.types' => 'nullable|array',
            'filter.types.*' => 'string|in:' . implode(',', array_keys(\App\Models\Product::Types)),
            'filter.categories' => 'nullable|array',
            'filter.categories.*' => 'integer',
            'filter.suppliers' => 'nullable|array',
            'filter.suppliers.*' => 'integer',
        ]);

        $data['filter']['status'] = $data['filter']['status'] ?? 'all';
        $data['filter']['types'] = $data['filter']['types'] ?? [];
        $data['filter']['categories'] = $data['filter']['categories'] ?? [];
        $data['filter']['suppliers'] = $data['filter']['suppliers'] ?? [];

        $q = Product::query();

        // Inisialisasi daftar kolom yang akan di-select
        $queryColumns = $data['columns'];

        // 💡 1. EAGER LOADING CATEGORY
        if (in_array('category', $data['columns'])) {
            $q->with('category');
            // Hapus 'category' dari daftar select (karena ini bukan kolom di tabel 'products')
            $queryColumns = array_diff($queryColumns, ['category']);
            // Pastikan foreign key-nya ADA di daftar select
            if (!in_array('category_id', $queryColumns)) {
                $queryColumns[] = 'category_id';
            }
        }

        // 💡 2. EAGER LOADING SUPPLIER
        if (in_array('supplier', $data['columns'])) {
            $q->with('supplier');
            // Hapus 'supplier' dari daftar select
            $queryColumns = array_diff($queryColumns, ['supplier']);
            // Pastikan foreign key-nya ADA di daftar select
            if (!in_array('supplier_id', $queryColumns)) {
                $queryColumns[] = 'supplier_id';
            }
        }

        $filter = $data['filter'];
        if (!empty($filter['status']) && $filter['status'] != 'all') {
            $q->where('products.active', $filter['status'] == 'active');
        }

        if (!empty($filter['types'])) {
            $q->whereIn('type', $filter['types']);
        }

        if (!empty($filter['categories'])) {
            $q->whereIn('category_id', $filter['categories']);
        }

        if (!empty($filter['suppliers'])) {
            $q->whereIn('supplier_id', $filter['suppliers']);
        }

        // 💡 3. HANDLE STOCK + UOM (Sudah benar)
        if (in_array('stock', $data['columns']) && !in_array('uom', $queryColumns)) {
            $queryColumns[] = 'uom';
        }

        // 💡 4. WAJIB SELECT: Pastikan Primary Key (id) selalu ada
        // Eloquent membutuhkannya untuk relasi dan model.
        if (!in_array('id', $queryColumns)) {
            $queryColumns[] = 'id';
        }

        $data['categories'] = $this->commonDataService->getProductCategories();
        $data['suppliers']  = $this->commonDataService->getSuppliers();
        $data['query_columns'] = $queryColumns;

        return $this->generateReport(
            'modules.admin.pages.reports.product.list',
            $data,
            $q
        );
    }

    // Modules\Admin\Http\Controllers\Reports\ProductReportController

    protected function processQuery(\Illuminate\Database\Eloquent\Builder $q, $columns)
    {
        $sortOptions = request('sortOptions') ?? [];
        $queryColumns = $columns;

        // Flag untuk melacak apakah join sudah dilakukan
        $joinedTables = [];

        // Pastikan 'uom' ditambahkan jika 'stock' diminta
        if (in_array('stock', $columns) && !in_array('uom', $queryColumns)) {
            $queryColumns[] = 'uom';
        }

        // --- 1. Proses Sorting ---
        foreach ($sortOptions as $option) {
            $column = $option['column'];
            $order = $option['order'];

            switch ($column) {
                case 'category':
                    // Cek apakah tabel 'product_categories' sudah di-join
                    if (!isset($joinedTables['category'])) {
                        // LEFT JOIN ke tabel kategori
                        $q->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id');
                        $joinedTables['category'] = true;
                    }
                    // Urutkan berdasarkan nama kategori
                    $q->orderBy('product_categories.name', $order);
                    break;

                case 'supplier':
                    // Cek apakah tabel 'suppliers' sudah di-join
                    if (!isset($joinedTables['supplier'])) {
                        // LEFT JOIN ke tabel supplier
                        $q->leftJoin('suppliers', 'products.supplier_id', '=', 'suppliers.id');
                        $joinedTables['supplier'] = true;
                    }
                    // Urutkan berdasarkan nama supplier
                    $q->orderBy('suppliers.name', $order);
                    break;

                default:
                    // Urutan untuk kolom biasa (kolom di tabel 'products')
                    $q->orderBy($column, $order);
                    break;
            }
        }

        // --- 2. Hapus Konflik Select ---
        // Jika ada join, kita harus secara eksplisit menyebutkan kolom dari tabel utama
        // untuk menghindari konflik nama kolom seperti 'id' atau 'name'.

        // Tambahkan prefix 'products.' ke semua kolom utama
        $selects = collect($queryColumns)->map(fn($col) => "products.{$col}")->toArray();

        // Tambahkan kembali kolom relasi yang dibutuhkan untuk eager loading jika ada join
        // Catatan: Jika Anda menggunakan with(), Anda harus berhati-hati dengan select() dan join().
        // Eager loading (with) lebih disukai daripada join untuk mengambil data relasi.
        // Tetapi join diperlukan untuk sorting.

        // Ketika menggunakan join untuk sorting, Anda harus memasukkan semua kolom yang dibutuhkan
        // ke dalam select, terutama yang berasal dari tabel relasi (product_categories, suppliers)
        // yang mungkin Anda butuhkan untuk sorting atau tampilan, namun kali ini kita hanya fokus
        // pada kolom yang di-select oleh $queryColumns.

        // Kita harus menyertakan id tabel utama (products.id)
        if (!in_array('products.id', $selects)) {
            $selects[] = 'products.id';
        }

        // Jika relasi di-eager load di generate() (menggunakan with()), pastikan Foreign Key ada.
        // Jika Anda sudah menambahkan category_id dan supplier_id di generate(),
        // pastikan keduanya juga diawali prefix 'products.' di sini jika join dilakukan.

        return $q->select($selects)->get();
    }
}
