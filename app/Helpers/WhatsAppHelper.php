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

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class WhatsAppHelper
{
    public static function sendMessage(string $receiver, string $message): array
    {
        $apiKey = config('services.whatsapp.api_key', env('WHATSAPP_API_KEY'));
        $endpoint = rtrim(config('services.whatsapp.endpoint', env('WHATSAPP_API_ENDPOINT')));

        if (empty($endpoint)) {
            throw new Exception("Invalid WhatsApp endpoint");
        }

        // 🧹 Bersihkan nomor telepon
        $receiver = self::normalizePhoneNumber($receiver);

        // 🚫 Validasi nomor telepon
        if (!self::isValidPhoneNumber($receiver)) {
            throw new Exception("Invalid phone number format: {$receiver}");
        }

        // pastikan URL endpoint lengkap
        $endpoint .= '/send-message';

        $body = [
            'api_key'  => $apiKey,
            'receiver' => $receiver,
            'data'     => ['message' => $message],
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept'       => '*/*',
            ])
                ->timeout(30)
                ->post($endpoint, $body);

            if ($response->successful()) {
                return [
                    'status' => true,
                    'code'   => $response->status(),
                    'data'   => $response->json(),
                ];
            }

            Log::warning('WhatsApp API non-success response', [
                'receiver' => $receiver,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);

            return [
                'status'  => false,
                'code'    => $response->status(),
                'message' => 'Provider returned non-success status',
                'body'    => $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsAppHelper::sendMessage failed', [
                'receiver' => $receiver,
                'error'    => $e->getMessage(),
            ]);

            return [
                'status'  => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Normalisasi nomor telepon ke format internasional Indonesia (62)
     */
    protected static function normalizePhoneNumber(string $number): string
    {
        // hilangkan semua karakter non-digit
        $clean = preg_replace('/[^0-9]/', '', $number);

        // kalau mulai dari 0 → ubah ke 62
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        }

        return $clean;
    }

    /**
     * Validasi nomor telepon dasar
     */
    protected static function isValidPhoneNumber(string $number): bool
    {
        // harus hanya angka dan panjang 9–15 digit
        return preg_match('/^[0-9]{9,15}$/', $number);
    }
}
