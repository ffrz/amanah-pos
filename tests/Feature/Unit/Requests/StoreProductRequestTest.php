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

namespace Tests\Unit\Requests;

use App\Http\Requests\Api\Product\StoreProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class StoreProductRequestTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $category;
    protected $supplier;

    /**
     * Helper method to create a StoreProductRequest instance.
     * It can optionally set a user resolver.
     *
     * IMPORTANT: For guest tests, pass null for $user.
     * For logged-in tests, pass the User object.
     */
    private function createRequest(array $data = [], ?User $user = null): StoreProductRequest
    {
        $symfonyRequest = SymfonyRequest::create('/', 'POST', $data);
        $request = StoreProductRequest::createFromBase($symfonyRequest);

        // Always set the user resolver based on the provided $user parameter
        $request->setUserResolver(fn() => $user);

        return $request;
    }

    protected function setUp(): void
    {
        parent::setUp();

        UserFactory::$defaultPassword = '12345';

        // Kita akan membuat user di sini, TAPI TIDAK AKAN memanggil actingAs()
        // Kita akan secara eksplisit meneruskan user ke createRequest() saat dibutuhkan.
        $this->user = User::factory()->create([
            'role' => User::Role_Admin,
            'active' => true,
        ]);

        $this->category = ProductCategory::factory()->create(['id' => 1]);
        $this->supplier = Supplier::factory()->create(['id' => 1]);
    }

    /** @test */
    public function it_authorizes_logged_in_users()
    {
        // Teruskan user yang sudah dibuat ke request
        $request = $this->createRequest([], $this->user);
        $this->assertTrue($request->authorize());
    }

    /** @test */
    public function it_fails_authorization_for_guest_users()
    {
        // Teruskan null ke request untuk mensimulasikan guest
        $request = $this->createRequest([], null);
        $this->assertFalse($request->authorize());
    }

    /**
     * @test
     * @dataProvider validationDataProvider
     */
    public function it_validates_the_request_data(array $data, array $expectedErrors = [], callable $setupCallback = null)
    {
        if ($setupCallback) {
            $setupCallback();
        }

        // Teruskan user yang sudah login ($this->user) ke request untuk validasi
        $request = $this->createRequest($data, $this->user);

        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        if (empty($expectedErrors)) {
            $this->assertTrue($validator->passes(), "Validation was expected to pass but failed: " . json_encode($validator->errors()->all()));
        } else {
            $this->assertTrue($validator->fails(), "Validation was expected to fail but passed. Errors: " . json_encode($validator->errors()->all()));
            $this->assertEqualsCanonicalizing($expectedErrors, $validator->errors()->keys(), "Expected errors mismatch.");
        }
    }

    public static function validationDataProvider(): array
    {
        return [
            'valid_data' => [
                [
                    'name' => 'Produk Baru Unik',
                    'category_id' => 1,
                    'supplier_id' => 1,
                    'type' => Product::Type_Stocked,
                    'barcode' => 'BAR123',
                    'stock' => 10,
                    'price' => 10000,
                    'active' => true,
                ],
                [],
                null,
            ],
            'required_name_missing' => [
                [
                    'category_id' => 1,
                ],
                ['name'],
                null,
            ],
            'invalid_category_id' => [
                [
                    'name' => 'Test Produk',
                    'category_id' => 999,
                ],
                ['category_id'],
                null,
            ],
            'invalid_type' => [
                [
                    'name' => 'Test Produk',
                    'type' => 'invalid_type',
                ],
                ['type'],
                null,
            ],
            'negative_stock' => [
                [
                    'name' => 'Test Produk',
                    'stock' => -5,
                ],
                ['stock'],
                null,
            ],
            'duplicate_name' => [
                [
                    'name' => 'Existing Product Name',
                ],
                ['name'],
                function () {
                    Product::factory()->create(['name' => 'Existing Product Name']);
                }
            ],
        ];
    }
}
