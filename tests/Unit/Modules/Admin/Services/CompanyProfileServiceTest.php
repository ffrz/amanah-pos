<?php

namespace Tests\Unit\Modules\Admin\Services;

use Mockery; // Import Mockery
use App\Helpers\ImageUploaderHelper;
use App\Models\Setting;
use App\Models\UserActivityLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Services\CompanyProfileService;
use Modules\Admin\Services\UserActivityLogService;
use Tests\TestCase;

// Ini adalah contoh Unit Test untuk CompanyProfileService.

class CompanyProfileServiceTest extends TestCase
{
    protected CompanyProfileService $service;
    protected $userActivityLogServiceMock;

    // Properti untuk menyimpan mock Setting dan ImageUploaderHelper
    protected $settingMock;
    protected $imageUploaderMock;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Mock Setting Model/Class (Overload untuk static calls)
        $this->settingMock = Mockery::mock('overload:' . Setting::class);

        // Setup default value untuk Setting::value()
        $this->settingMock->shouldReceive('value')
            ->andReturnUsing(function ($key, $default = null) {
                // Memberikan data 'lama' yang stabil untuk pengujian
                $map = [
                    'company.name' => 'Old Company Name',
                    'company.headline' => 'Old Tagline',
                    'company.phone' => '0812345678',
                    'company.address' => 'Old Address',
                    'company.logo_path' => 'path/to/old/logo.png',
                ];
                return $map[$key] ?? $default;
            })
            ->byDefault();

        $this->settingMock->shouldReceive('refreshAll')
            ->byDefault();

        // 2. Mock ImageUploaderHelper (Overload untuk static calls)
        $this->imageUploaderMock = Mockery::mock('overload:' . ImageUploaderHelper::class);
        $this->imageUploaderMock->shouldReceive('uploadAndResize')->byDefault();
        $this->imageUploaderMock->shouldReceive('deleteImage')->byDefault();

        // 3. Mock UserActivityLogService (Dependensi Constructor)
        $this->userActivityLogServiceMock = $this->createMock(UserActivityLogService::class);

        // 4. Inisialisasi service yang akan diuji
        $this->service = new CompanyProfileService($this->userActivityLogServiceMock);

        // 5. Mock DB Facade (untuk transaksi)
        DB::shouldReceive('beginTransaction')->byDefault();
        DB::shouldReceive('commit')->byDefault();
        DB::shouldReceive('rollBack')->byDefault();
    }

    // **PENTING: Pastikan Mockery dibersihkan setelah setiap test**
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test case: Memastikan update berhasil dilakukan saat ada perubahan data.
     */
    public function test_update_profile_successfully_when_data_changes()
    {
        $validatedData = [
            'name' => 'New Company Name', // Berubah
            'headline' => 'New Tagline',    // Berubah
            'phone' => '0812345678',
            'address' => 'Old Address',
            'logo_path' => 'path/to/old/logo.png',
        ];
        $logoFile = null;

        // FIX: Pisahkan ekspektasi Setting::setValue untuk setiap panggilan.
        // Ini memastikan Mockery mencocokkan setiap set argumen secara individual.
        $this->settingMock->shouldReceive('setValue')
            ->once()->with('company.name', $validatedData['name']);

        $this->settingMock->shouldReceive('setValue')
            ->once()->with('company.headline', $validatedData['headline']);

        $this->settingMock->shouldReceive('setValue')
            ->once()->with('company.phone', $validatedData['phone']);

        $this->settingMock->shouldReceive('setValue')
            ->once()->with('company.address', $validatedData['address']);

        $this->settingMock->shouldReceive('setValue')
            ->once()->with('company.logo_path', $validatedData['logo_path']);

        // Ekspektasi: DB::commit harus dipanggil
        DB::shouldReceive('commit')->once();

        // Ekspektasi: Logging harus dipanggil
        $this->userActivityLogServiceMock->expects($this->once())
            ->method('log')
            ->with(
                UserActivityLog::Category_Settings,
                UserActivityLog::Name_UpdateCompanyProfile,
                'Pengaturan profil perusahaan telah diperbarui.',
                $this->anything()
            );

        $result = $this->service->updateProfile($validatedData, $logoFile);

        $this->assertTrue($result, 'Service harus mengembalikan true saat update berhasil.');
    }

    /**
     * Test case: Memastikan proses dilewati jika data lama dan baru identik.
     */
    public function test_update_profile_returns_false_if_no_changes_detected()
    {
        // Data yang sama persis dengan yang di-mock di setUp()
        $validatedData = [
            'name' => 'Old Company Name',
            'headline' => 'Old Tagline',
            'phone' => '0812345678',
            'address' => 'Old Address',
            'logo_path' => 'path/to/old/logo.png',
        ];
        $logoFile = null;

        // Ekspektasi: Setting::setValue tidak boleh dipanggil sama sekali
        $this->settingMock->shouldNotReceive('setValue');

        // Ekspektasi: DB::commit tidak boleh dipanggil
        DB::shouldNotReceive('commit');

        // Ekspektasi: Logging tidak boleh dipanggil
        $this->userActivityLogServiceMock->expects($this->never())
            ->method('log');

        $result = $this->service->updateProfile($validatedData, $logoFile);

        $this->assertFalse($result, 'Service harus mengembalikan false jika tidak ada perubahan.');
    }

    /**
     * Test case: Memastikan transaksi di-rollback saat terjadi Exception DB.
     */
    public function test_update_profile_handles_database_exception_and_rolls_back()
    {
        $this->expectException(\Exception::class); // Ekspektasi: Exception dilempar kembali

        $validatedData = [
            'name' => 'Data that will fail',
            'headline' => 'Old Tagline',
            'phone' => '0812345678',
            'address' => 'Old Address',
            'logo_path' => 'path/to/old/logo.png',
        ];
        $logoFile = null;

        // Setup: Paksa salah satu panggilan Setting::setValue melempar Exception
        $this->settingMock->shouldReceive('setValue')
            ->with('company.name', $validatedData['name'])
            ->once()
            ->andThrow(new \Exception('Simulasi kegagalan DB'));

        // Ekspektasi: DB::rollBack harus dipanggil
        DB::shouldReceive('rollBack')->once();

        // Ekspektasi: DB::commit tidak boleh dipanggil
        DB::shouldNotReceive('commit');

        // Panggil method yang diuji
        $this->service->updateProfile($validatedData, $logoFile);
    }

    /**
     * Test case: Memastikan logo baru diunggah dan logo lama diabaikan.
     */
    public function test_update_profile_uploads_new_logo()
    {
        $validatedData = [
            'name' => 'Old Company Name',
            'headline' => 'Old Tagline',
            'phone' => '0812345678',
            'address' => 'Old Address',
            'logo_path' => 'path/to/old/logo.png',
        ];

        // Mock file upload
        $logoFileMock = $this->createMock(UploadedFile::class);
        $newPath = 'path/to/new/logo.jpg';

        // Ekspektasi: ImageUploaderHelper::uploadAndResize dipanggil
        $this->imageUploaderMock->shouldReceive('uploadAndResize')
            ->once()
            ->with($logoFileMock, 'company', 'path/to/old/logo.png', 240, 240)
            ->andReturn($newPath);

        // FIX: Pisahkan ekspektasi Setting::setValue. Kita harapkan semua field di-set, 
        // dengan logo_path menerima nilai path baru.
        $this->settingMock->shouldReceive('setValue')
            ->once()->with('company.name', $validatedData['name']);

        $this->settingMock->shouldReceive('setValue')
            ->once()->with('company.headline', $validatedData['headline']);

        $this->settingMock->shouldReceive('setValue')
            ->once()->with('company.phone', $validatedData['phone']);

        $this->settingMock->shouldReceive('setValue')
            ->once()->with('company.address', $validatedData['address']);

        $this->settingMock->shouldReceive('setValue')
            ->once()->with('company.logo_path', $newPath); // Menggunakan $newPath

        $result = $this->service->updateProfile($validatedData, $logoFileMock);

        $this->assertTrue($result);
    }

    /**
     * Test case: Memastikan logo lama dihapus jika logo_path dihilangkan dari input.
     */
    public function test_update_profile_deletes_old_logo_if_path_is_empty()
    {
        // Data dikirim tanpa logo_path (atau null/kosong) dan tanpa logo_image baru
        $validatedData = [
            'name' => 'New Name', // Ada perubahan data agar update dijalankan
            'headline' => 'Old Tagline',
            'phone' => '0812345678',
            'address' => 'Old Address',
            'logo_path' => null, // Skenario penghapusan
        ];
        $logoFile = null;

        // Ekspektasi: ImageUploaderHelper::deleteImage dipanggil dengan path lama
        $this->imageUploaderMock->shouldReceive('deleteImage')
            ->once()
            ->with('path/to/old/logo.png');

        // FIX: Pisahkan ekspektasi Setting::setValue.
        $this->settingMock->shouldReceive('setValue')
            ->once()->with('company.name', $validatedData['name']);

        $this->settingMock->shouldReceive('setValue')
            ->once()->with('company.headline', $validatedData['headline']);

        $this->settingMock->shouldReceive('setValue')
            ->once()->with('company.phone', $validatedData['phone']);

        $this->settingMock->shouldReceive('setValue')
            ->once()->with('company.address', $validatedData['address']);

        $this->settingMock->shouldReceive('setValue')
            ->once()->with('company.logo_path', ''); // Memastikan disimpan sebagai string kosong

        $result = $this->service->updateProfile($validatedData, $logoFile);

        $this->assertTrue($result);
    }
}
