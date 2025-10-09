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

namespace Tests\Unit\App\Models;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;
use ReflectionClass;


/**
 * Menggunakan alias mocking dan memastikan properti statis di-reset penuh 
 * untuk mengatasi error "class already exists" saat memalsukan panggilan statis pada model.
 */
class SettingTest extends TestCase
{
    // Properti untuk mock Setting (menggunakan alias)
    protected $settingMock;

    // Data dummy untuk pengujian
    protected $testSettingsArray = [
        'app.name' => 'MyApp',
        'app.version' => '1.0.0',
        'email.contact' => 'contact@app.com',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Force-reset properti statis Setting model
        // Ini KUNCI untuk membersihkan cache internal ($settings, $is_initialized).
        $this->resetSettingStatics();

        // 2. Mock Setting Model menggunakan 'alias:'
        // Alias membuat mock class yang namanya sama dengan App\Models\Setting.
        // Kita menggunakan string namespace penuh di sini.
        $this->settingMock = Mockery::mock('alias:\App\Models\Setting');
    }

    protected function tearDown(): void
    {
        // Tutup Mockery setelah setiap test selesai
        Mockery::close();

        // Reset statis lagi setelah test selesai
        $this->resetSettingStatics();
        parent::tearDown();
    }

    /**
     * Menggunakan Reflection untuk mengatur ulang variabel cache statis pada model Setting.
     */
    private function resetSettingStatics()
    {
        // Menggunakan ReflectionClass untuk mengakses properti statis privat
        $reflection = new ReflectionClass(Setting::class);

        // Reset $settings (static $settings = [])
        if ($reflection->hasProperty('settings')) {
            $settingsProp = $reflection->getProperty('settings');
            $settingsProp->setAccessible(true);
            $settingsProp->setValue(null, []);
        }

        // Reset $is_initialized (static $is_initialized = false)
        if ($reflection->hasProperty('is_initialized')) {
            $initializedProp = $reflection->getProperty('is_initialized');
            $initializedProp->setAccessible(true);
            $initializedProp->setValue(null, false);
        }
    }

    /**
     * Helper untuk memalsukan hasil dari Setting::all()
     *
     * @param array $data Key-value array of settings to return.
     */
    private function mockAllSettings(array $data)
    {
        $collection = new Collection();
        foreach ($data as $key => $value) {
            // Mimik objek database row
            $item = Mockery::mock('StdClass');
            $item->key = $key;
            $item->value = $value;
            $collection->push($item);
        }

        // Ekspektasi: Setting::all() dipanggil tepat satu kali
        // Panggil mock statis pada Setting
        Setting::shouldReceive('all')
            ->once()
            ->andReturn($collection);
    }

    // --- Tests start here ---

    /**
     * Test: Memastikan pemanggilan value() pertama kali memuat semua data dari DB ke cache.
     */
    public function test_init_loads_all_settings_from_database()
    {
        // 1. Setup mock data untuk Setting::all()
        $this->mockAllSettings($this->testSettingsArray);

        // 2. Panggil value(), yang akan memicu _init()
        Setting::value('app.name');

        // 3. Assert cache terisi
        $this->assertEquals($this->testSettingsArray['app.version'], Setting::value('app.version'));
        $this->assertEquals($this->testSettingsArray, Setting::values());
    }

    /**
     * Test: Memastikan value() mengembalikan nilai yang benar dari cache.
     */
    public function test_value_returns_correct_setting_after_init()
    {
        $this->mockAllSettings($this->testSettingsArray);

        // Panggilan pertama akan memuat cache
        $name = Setting::value('app.name');

        $this->assertEquals($this->testSettingsArray['app.name'], $name);
    }

    /**
     * Test: Memastikan value() mengembalikan nilai default ketika kunci tidak ditemukan.
     */
    public function test_value_returns_default_when_key_not_found()
    {
        $this->mockAllSettings($this->testSettingsArray);

        // Memuat cache
        Setting::value('app.name');

        $default = 'Default Value';
        $result = Setting::value('non.existent.key', $default);

        $this->assertEquals($default, $result);
    }

    /**
     * Test: Memastikan values() mengembalikan seluruh array pengaturan yang di-cache.
     */
    public function test_values_returns_all_cached_settings()
    {
        $this->mockAllSettings($this->testSettingsArray);

        // Panggilan values() akan memuat cache
        $settings = Setting::values();

        $this->assertEquals($this->testSettingsArray, $settings);
    }

    /**
     * Test: Memastikan setValue() memanggil updateOrCreate dan memperbarui cache statis.
     */
    public function test_set_value_updates_database_and_cache()
    {
        $key = 'new.feature';
        $value = 'enabled';

        // 1. Ekspektasi: Panggilan ke updateOrCreate() harus terjadi
        Setting::shouldReceive('updateOrCreate')
            ->once()
            ->with(['key' => $key], ['value' => $value]);

        // 2. Panggil method yang diuji
        Setting::setValue($key, $value);

        // 3. Assert cache diperbarui
        $this->assertEquals($value, Setting::value($key, 'fail'));

        // 4. Pastikan Setting::all() tidak dipanggil setelahnya
        Setting::shouldNotHaveReceived('all');
    }

    /**
     * Test: Memastikan refreshAll() mereset cache dan memuat ulang data dari DB.
     */
    public function test_refresh_all_forces_reinitialization()
    {
        // 1. Setup A: Cache initial (dipanggil sekali)
        $initialData = ['test.key' => 'initial_value'];
        $this->mockAllSettings($initialData); // Set up mock 'all' pertama
        Setting::value('test.key');
        $this->assertEquals('initial_value', Setting::value('test.key'));

        // 2. Setup B: Data baru untuk pemuatan ulang
        // Set up mock 'all' kedua
        Setting::shouldReceive('all')
            ->once()
            ->andReturn(
                (new Collection([
                    (object)['key' => 'test.key', 'value' => 'updated_value'],
                    (object)['key' => 'new.key', 'value' => '123'],
                ]))
            );

        // 3. Panggil method yang diuji
        Setting::refreshAll();

        // 4. Assert cache telah dimuat ulang
        $this->assertEquals('updated_value', Setting::value('test.key'));
        $this->assertEquals('123', Setting::value('new.key'));
        $this->assertCount(2, Setting::values());
    }
}
