<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // seharusnya parent_ref_id
            if (!Schema::hasColumn('stock_movements', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')
                    ->nullable()
                    ->index()
                    ->after('ref_type')
                    ->comment('ID dokumen induk, misalnya SO/PO/SA');
            }

            if (!Schema::hasColumn('stock_movements', 'parent_ref_type')) {
                $table->string('parent_ref_type', 50)
                    ->nullable()
                    ->index()
                    ->after('parent_id')
                    ->comment('Tipe dokumen induk: sales_order, purchase_order, stock_adjustment, dll');
            }


            if (!Schema::hasColumn('stock_movements', 'party_id')) {
                $table->unsignedBigInteger('party_id')
                    ->nullable()
                    ->index()
                    ->after('created_by')
                    ->comment('ID customer atau supplier');
            }

            if (!Schema::hasColumn('stock_movements', 'party_type')) {
                $table->string('party_type', 20)
                    ->nullable()
                    ->index()
                    ->after('party_id')
                    ->comment('Jenis party: customer / supplier');
            }

            if (!Schema::hasColumn('stock_movements', 'party_name')) {
                $table->string('party_name', 255)
                    ->nullable()
                    ->after('party_type')
                    ->comment('Nama customer/supplier (snapshot)');
            }


            if (!Schema::hasColumn('stock_movements', 'document_code')) {
                $table->string('document_code', 100)
                    ->nullable()
                    ->index()
                    ->after('party_name')
                    ->comment('Nomor dokumen induk (misalnya SO-2025-0012)');
            }

            if (!Schema::hasColumn('stock_movements', 'document_date')) {
                $table->date('document_date')
                    ->nullable()
                    ->after('document_code')
                    ->comment('Tanggal dokumen induk');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn([
                'parent_id',
                'parent_ref_type',
                'party_id',
                'party_type',
                'party_name',
                'document_code',
                'document_date',
            ]);
        });
    }
};
