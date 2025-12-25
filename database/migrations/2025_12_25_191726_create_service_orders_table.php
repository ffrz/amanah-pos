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
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique();
            $table->string('order_status')->nullable()->default(null);
            $table->string('service_status')->nullable()->default(null);
            $table->string('payment_status')->nullable()->default(null);
            $table->string('repair_status')->nullable()->default(null);

            // order
            $table->datetime('closed_datetime')->nullable()->default(null);
            $table->foreignId('closed_by_uid')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('received_by_uid')->nullable()->constrained('users')->onDelete('set null');

            // customer info
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->string('customer_name', 100);
            $table->string('customer_phone', 100);
            $table->string('customer_address', 500);

            // device info
            $table->string('device_type', 100);
            $table->string('device', 100);
            $table->string('equipments', 200);
            $table->string('device_sn', 100);

            // service info
            $table->string('problems', 500);
            $table->string('actions', 500);
            $table->dateTime('received_datetime')->nullable()->default(null);
            $table->dateTime('checked_datetime')->nullable()->default(null);
            $table->dateTime('worked_datetime')->nullable()->default(null);
            $table->dateTime('completed_datetime')->nullable()->default(null);
            $table->dateTime('picked_datetime')->nullable()->default(null);

            // garansi boleh dimulai sejak tanggal selesai, tapi perlu ada field tersendiri
            $table->date('warranty_start_date')->nullable()->default(null);
            $table->unsignedSmallInteger('warranty_day_count')->default(0); // jumlah hari garansi

            // cost and payment
            $table->decimal('down_payment', 12, 0)->default(0.);
            $table->decimal('estimated_cost', 12, 0)->default(0.);
            $table->decimal('total_cost', 12, 0)->default(0.);

            $table->json('images')->nullable();

            // extra
            $table->foreignId('technician_id')->nullable()->constrained('service_technicians')->onDelete('set null');
            $table->text('notes');

            $table->createdUpdatedDeletedTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
