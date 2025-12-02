<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_movements', 'party_code')) {
                $table->string('party_code', 50)
                    ->nullable()
                    ->after('party_type')
                    ->index();
            }

            if (!Schema::hasColumn('stock_movements', 'document_datetime')) {
                $table->dateTime('document_datetime')
                    ->nullable()
                    ->after('document_code');
            }

            if (Schema::hasColumn('stock_movements', 'document_date')) {
                DB::statement("
                    UPDATE stock_movements
                    SET document_datetime =
                        CASE
                            WHEN document_date IS NOT NULL
                            THEN CONCAT(document_date, ' 00:00:00')
                            ELSE NULL
                        END
                ");
            }
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            if (Schema::hasColumn('stock_movements', 'document_date')) {
                $table->dropColumn('document_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn('party_code');

            // Kembalikan kolom lama
            $table->date('document_date')->nullable()->after('document_code');

            // Copy back jika ingin
            DB::statement("
                UPDATE stock_movements
                SET document_date = DATE(document_datetime)
                WHERE document_datetime IS NOT NULL
            ");

            $table->dropColumn('document_datetime');
        });
    }
};
