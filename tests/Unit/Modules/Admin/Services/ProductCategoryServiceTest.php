<?php

namespace Tests\Unit\Modules\Admin\Services;

use Tests\TestCase;
use App\Models\ProductCategory;
use App\Models\UserActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Admin\Services\ProductCategoryService;
use Modules\Admin\Services\UserActivityLogService;
use App\Exceptions\ModelNotModifiedException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

/**
 * Pengujian Integrasi untuk ProductCategoryService.
 * Fokus pada logika bisnis dan interaksi dengan database (Integrasi)
 * dengan mocking dependency (Unit).
 */
class ProductCategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ProductCategoryService $productCategoryService;
    protected $userActivityLogServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mocking dependency untuk memastikan log activity dipanggil dengan benar
        // Tanpa benar-benar menyentuh database log aktivitas.
        $this->userActivityLogServiceMock = Mockery::mock(UserActivityLogService::class);

        // FIX: Izinkan pemanggilan method 'log' secara default. Ini diperlukan karena 
        // ProductCategory::factory()->create() mungkin memicu event observer yang 
        // memanggil service ini secara tidak sengaja di luar scope assertion logging.
        $this->userActivityLogServiceMock->shouldReceive('log')->byDefault();

        // Inject mock ke dalam service
        $this->productCategoryService = new ProductCategoryService(
            $this->userActivityLogServiceMock
        );

        // Asumsi Product Category Factory sudah ada dan berjalan.
        if (!class_exists(\Database\Factories\ProductCategoryFactory::class)) {
            // Fallback jika factory tidak tersedia, tambahkan data secara manual jika perlu.
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // --- TEST LOGIC FIND/FINDORCREATE (100% Coverage) ---

    #[Test]
    public function findOrCreate_returns_new_model_if_id_is_null()
    {
        // ACT
        $category = $this->productCategoryService->findOrCreate(null);

        // ASSERT
        $this->assertInstanceOf(ProductCategory::class, $category);
        $this->assertFalse($category->exists);
        $this->assertNull($category->id);
    }

    #[Test]
    public function findOrCreate_returns_existing_category_if_id_is_provided()
    {
        // ARRANGE: Buat kategori di DB
        $data = ['name' => 'Existing Category', 'description' => 'Untuk diuji findOrCreate'];
        $existingCategory = $this->productCategoryService->findOrCreate(null);
        $existingCategory = $this->productCategoryService->save($existingCategory, $data);

        // ACT
        $foundCategory = $this->productCategoryService->findOrCreate($existingCategory->id);

        // ASSERT
        $this->assertEquals($existingCategory->id, $foundCategory->id);
        $this->assertEquals($data['name'], $foundCategory->name);

        // Verifikasi array sama, mengabaikan timestamps (solusi dari diskusi sebelumnya)
        $ignoredKeys = ['created_at', 'updated_at', 'id', 'created_by', 'updated_by'];
        $this->assertEquals(
            collect($existingCategory->toArray())->except($ignoredKeys)->all(),
            collect($foundCategory->toArray())->except($ignoredKeys)->all(),
            'Data kategori yang ditemukan tidak sama dengan data yang disimpan.'
        );
    }

    #[Test]
    public function find_should_return_existing_category()
    {
        // ARRANGE
        $category = ProductCategory::factory()->create(['name' => 'Find Test']);

        // ACT
        $found = $this->productCategoryService->find($category->id);

        // ASSERT
        $this->assertEquals($category->id, $found->id);
        $this->assertEquals('Find Test', $found->name);
    }

    #[Test]
    public function find_should_throw_exception_when_not_found()
    {
        // ASUMSI: ID 9999 tidak akan pernah ada
        $this->expectException(ModelNotFoundException::class);
        $this->productCategoryService->find(9999);
    }

    // --- TEST LOGIC DUPLICATE (100% Coverage) ---

    #[Test]
    public function duplicate_should_return_replicated_model()
    {
        // ARRANGE
        $original = ProductCategory::factory()->create(['name' => 'Original Item']);

        // ACT
        $duplicate = $this->productCategoryService->duplicate($original->id);

        // ASSERT
        $this->assertFalse($duplicate->exists, 'Duplikasi harus berupa model baru');
        $this->assertNotEquals($original->id, $duplicate->id, 'ID harus berbeda');
        $this->assertStringContainsString('Original Item', $duplicate->name, 'Nama harus diduplikasi');
    }

    #[Test]
    public function duplicate_should_throw_exception_when_original_not_found()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->productCategoryService->duplicate(9999);
    }


    // --- TEST LOGIC SAVE (100% Coverage) ---

    #[Test]
    public function save_should_create_new_category_and_log_activity()
    {
        // EXPECTATION LOG: Memastikan log create dipanggil
        $this->userActivityLogServiceMock->shouldReceive('log')
            ->once()
            ->with(
                UserActivityLog::Category_ProductCategory,
                UserActivityLog::Name_ProductCategory_Create,
                Mockery::type('string'),
                Mockery::subset(['formatter' => 'product-category'])
            );

        // ARRANGE
        $category = new ProductCategory();
        $data = ['name' => 'Baju Baru', 'description' => 'Kategori baru untuk anak'];

        // ACT
        $savedCategory = $this->productCategoryService->save($category, $data);

        // ASSERT
        $this->assertTrue($savedCategory->exists);
        $this->assertDatabaseHas('product_categories', ['name' => 'Baju Baru', 'description' => 'Kategori baru untuk anak']);
    }

    #[Test]
    public function save_should_update_existing_category_and_log_activity()
    {
        // EXPECTATION LOG: Memastikan log update dipanggil
        $this->userActivityLogServiceMock->shouldReceive('log')
            ->once()
            ->with(
                UserActivityLog::Category_ProductCategory,
                UserActivityLog::Name_ProductCategory_Update,
                Mockery::type('string'),
                Mockery::subset(['formatter' => 'product-category'])
            );

        // ARRANGE
        $category = ProductCategory::factory()->create(['name' => 'Old Name']);
        $newData = ['name' => 'Updated Name', 'description' => 'Updated description'];

        // ACT
        $updatedCategory = $this->productCategoryService->save($category, $newData);

        // ASSERT
        $this->assertEquals('Updated Name', $updatedCategory->name);
        $this->assertDatabaseHas('product_categories', ['id' => $category->id, 'name' => 'Updated Name']);
        $this->assertDatabaseMissing('product_categories', ['id' => $category->id, 'name' => 'Old Name']);
    }

    #[Test]
    public function save_should_throw_exception_if_no_fields_are_modified()
    {
        // EXPECTATION EXCEPTION: Memastikan ModelNotModifiedException dilempar
        $this->expectException(ModelNotModifiedException::class);

        // ARRANGE
        $category = ProductCategory::factory()->create(['name' => 'No Change']);
        $data = ['name' => 'No Change', 'description' => $category->description]; // Data sama

        // ACT (Log activity tidak boleh dipanggil)
        $this->userActivityLogServiceMock->shouldNotReceive('log');
        $this->productCategoryService->save($category, $data);
    }

    // --- TEST LOGIC DELETE (100% Coverage) ---

    #[Test]
    public function delete_should_remove_category_and_log_activity()
    {
        // EXPECTATION LOG: Memastikan log delete dipanggil
        $this->userActivityLogServiceMock->shouldReceive('log')
            ->once()
            ->with(
                UserActivityLog::Category_ProductCategory,
                UserActivityLog::Name_ProductCategory_Delete,
                Mockery::type('string'),
                Mockery::subset(['formatter' => 'product-category'])
            );

        // ARRANGE
        $category = ProductCategory::factory()->create(['name' => 'Item to Delete']);

        // ACT
        $this->productCategoryService->delete($category);

        // ASSERT
        $this->assertDatabaseMissing('product_categories', ['id' => $category->id]);
    }

    // --- TEST LOGIC GETDATA (100% Coverage) ---

    #[Test]
    public function getData_returns_data_with_no_filters()
    {
        // ARRANGE
        ProductCategory::factory()->count(10)->create();
        $options = [
            'filter' => ['search' => null],
            'order_by' => 'name',
            'order_type' => 'asc',
            'per_page' => 5,
        ];

        // ACT
        $paginator = $this->productCategoryService->getData($options);

        // ASSERT
        $this->assertCount(5, $paginator->items());
        $this->assertEquals(10, $paginator->total());
    }

    #[Test]
    public function getData_returns_data_with_search_filter()
    {
        // ARRANGE
        ProductCategory::factory()->create(['name' => 'Meja Kursi']);
        ProductCategory::factory()->create(['name' => 'Sofa Kayu', 'description' => 'Kursi Santai']);
        ProductCategory::factory()->count(5)->create(); // Data noise

        $options = [
            'filter' => ['search' => 'Kursi'], // Search di name dan description
            'order_by' => 'id',
            'order_type' => 'asc',
            'per_page' => 10,
        ];

        // ACT
        $paginator = $this->productCategoryService->getData($options);

        // ASSERT: Hanya 2 kategori yang mengandung kata 'Kursi'
        $this->assertCount(2, $paginator->items());
        $this->assertEquals('Meja Kursi', $paginator->items()[0]->name);
        $this->assertEquals('Sofa Kayu', $paginator->items()[1]->name);
    }

    #[Test]
    public function getData_returns_data_ordered_by_name_desc()
    {
        // ARRANGE
        ProductCategory::factory()->create(['name' => 'Zebra']);
        ProductCategory::factory()->create(['name' => 'Apple']);
        ProductCategory::factory()->create(['name' => 'Banana']);

        $options = [
            'filter' => ['search' => null],
            'order_by' => 'name',
            'order_type' => 'desc', // Urutkan Z-A
            'per_page' => 10,
        ];

        // ACT
        $paginator = $this->productCategoryService->getData($options);

        // ASSERT: Urutan harus Zebra, Banana, Apple
        $this->assertCount(3, $paginator->items());
        $this->assertEquals('Zebra', $paginator->items()[0]->name);
        $this->assertEquals('Banana', $paginator->items()[1]->name);
        $this->assertEquals('Apple', $paginator->items()[2]->name);
    }
}
