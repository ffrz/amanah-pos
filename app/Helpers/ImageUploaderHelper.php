<?php

/**
 * MIT License
 * 
 * Copyright (c) 2025 Fahmi Fauzi Rahman
 * See LICENSE file in the project root for full license information.
 * 
 * GitHub: https://github.com/ffrz
 * Email: fahmifauzirahman@gmail.com
 */

namespace App\Helpers;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File; // Untuk operasi file sistem

class ImageUploaderHelper
{
    /**
     * Mengunggah dan mengubah ukuran gambar, secara opsional menghapus gambar lama.
     * Mengembalikan path relatif gambar yang baru diunggah.
     *
     * @param UploadedFile $file File gambar yang diunggah.
     * @param string $targetDirectory Direktori di dalam public/uploads/ (misalnya 'transactions').
     * @param string|null $oldImagePath Path relatif ke gambar lama yang akan dihapus (dari public/).
     * @param int $maxWidth Lebar maksimum untuk resize.
     * @param int $maxHeight Tinggi maksimum untuk resize.
     * @return string Path relatif gambar yang baru diunggah (misalnya 'uploads/transactions/filename.jpg').
     * @throws \Exception Jika terjadi kesalahan dalam proses gambar atau penyimpanan.
     */
    public static function uploadAndResize(
        UploadedFile $file,
        string $targetDirectory,
        ?string $oldImagePath = null,
        int $maxWidth = 1024,
        int $maxHeight = 1024
    ): string {
        // Hapus gambar lama jika disediakan dan ada
        if ($oldImagePath && File::exists(public_path($oldImagePath))) {
            File::delete(public_path($oldImagePath));
        }

        // Generate nama file unik
        $filename = time() . '_' . $file->getClientOriginalName();
        $fullTargetDir = public_path('uploads/' . $targetDirectory);
        $imagePath = 'uploads/' . $targetDirectory . '/' . $filename;

        // Pastikan direktori tujuan ada
        if (!File::isDirectory($fullTargetDir)) {
            File::makeDirectory($fullTargetDir, 0777, true, true);
        }

        // Resize dan simpan gambar
        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);

            $width = $image->width();
            $height = $image->height();
            $ratio = max($width / $maxWidth, $height / $maxHeight);

            if ($ratio > 1) {
                $newWidth = (int) round($width / $ratio);
                $newHeight = (int) round($height / $ratio);

                $image->resize($newWidth, $newHeight, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize(); // Hanya mengecilkan, tidak membesarkan
                });
            }
            $image->save(public_path($imagePath));
        } catch (\Throwable $th) {
            // Hapus file yang mungkin sudah di-upload jika terjadi error resize/save
            if (File::exists(public_path($imagePath))) {
                File::delete(public_path($imagePath));
            }
            throw $th; // Lempar ulang exception
        }

        return $imagePath;
    }

    /**
     * Menghapus file gambar dari penyimpanan.
     *
     * @param string|null $imagePath Path relatif ke gambar yang akan dihapus (dari public/).
     * @return bool True jika berhasil dihapus, false jika tidak ada file atau gagal.
     */
    public static function deleteImage(?string $imagePath): bool
    {
        if ($imagePath && File::exists(public_path($imagePath))) {
            return File::delete(public_path($imagePath));
        }
        return false;
    }
}
