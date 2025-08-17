<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class CompanyProfileController
 *
 * Controller ini bertanggung jawab untuk mengelola profil perusahaan,
 * termasuk menampilkan dan memperbarui informasi seperti nama, telepon, dan alamat perusahaan.
 * Akses ke controller ini dibatasi hanya untuk peran admin.
 */
class CompanyProfileController extends Controller
{
    /**
     * Konstruktor untuk CompanyProfileController.
     * Menerapkan middleware untuk otorisasi peran.
     */
    public function __construct()
    {
        allowed_roles(User::Role_Admin);
    }

    /**
     * Menampilkan form profil perusahaan.
     *
     * @return \Inertia\Response
     */
    public function edit()
    {
        // Mengambil nilai pengaturan perusahaan dari model Setting
        $data = [
            'name' => Setting::value('company_name', env('APP_NAME', 'Koperasiku')),
            'phone' => Setting::value('company_phone', ''),
            'address' => Setting::value('company_address', ''),
        ];

        // Mengirim data ke komponen Inertia
        return inertia('company-profile/Edit', compact('data'));
    }

    /**
     * Memperbarui informasi profil perusahaan.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request): \Illuminate\Http\RedirectResponse
    {
        // Mencatat aktivitas pengguna
        Auth::user()->setLastActivity('Memperbarui profil perusahaan');

        // Aturan validasi untuk input
        $rules = [
            'name' => 'required|string|min:2|max:100', // Tambahkan 'string'
            // Regex untuk nomor telepon bisa sangat kompleks. Pastikan regex ini sesuai kebutuhan.
            // Jika memungkinkan, gunakan library validasi telepon atau validasi yang lebih sederhana.
            'phone' => 'nullable|string|regex:/^(\+?\d{1,4})?[\s.-]?\(?\d{1,4}\)?[\s.-]?\d{1,4}[\s.-]?\d{1,9}$/|max:40',
            'address' => 'nullable|string|max:1000', // Tambahkan 'nullable' dan 'string'
        ];

        // Lakukan validasi request
        // request()->validate() akan otomatis mengarahkan kembali dengan error jika validasi gagal
        $validatedData = $request->validate($rules);

        // Pastikan nilai default untuk 'phone' dan 'address' jika tidak disediakan atau kosong
        // Ini memastikan database menyimpan string kosong bukan null jika kolom tidak nullable
        $name = $validatedData['name'];
        $phone = $validatedData['phone'] ?? '';
        $address = $validatedData['address'] ?? '';

        // Memperbarui pengaturan perusahaan
        // Asumsi Setting::setValue adalah metode yang benar untuk menyimpan pengaturan
        Setting::setValue('company_name', $name);
        Setting::setValue('company_phone', $phone);
        Setting::setValue('company_address', $address);

        // Mengembalikan respons dengan pesan sukses
        return back()->with('success', 'Profil perusahaan berhasil diperbarui.');
    }
}
