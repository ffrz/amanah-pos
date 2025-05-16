<?php

use App\Models\ServiceOrder;
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

            // statuses
            $table->enum('order_status', array_keys(ServiceOrder::OrderStatuses))->default(ServiceOrder::OrderStatus_Open);
            $table->enum('service_status', array_keys(ServiceOrder::ServiceStatuses))->default(ServiceOrder::ServiceStatus_Received);
            $table->enum('payment_status', array_keys(ServiceOrder::PaymentStatuses))->default(ServiceOrder::PaymentStatus_Unpaid);
            $table->enum('repair_status', array_keys(ServiceOrder::RepairStatuses))->default(ServiceOrder::RepairStatus_NotFinished);

            // order
            $table->datetime('created_datetime')->nullable();
            $table->unsignedBigInteger('created_by_uid')->nullable();
            $table->datetime('closed_datetime')->nullable();
            $table->unsignedBigInteger('closed_by_uid')->nullable();
            $table->datetime('updated_datetime')->nullable();
            $table->unsignedBigInteger('updated_by_uid')->nullable();

            // customer info
            $table->unsignedBigInteger('customer_id')->nullable(true)->default(null);
            $table->string('customer_name', 100);
            $table->string('customer_phone', 100);
            $table->string('customer_address', 200);

            // device info
            $table->string('device_type', 100);
            $table->string('device', 100);
            $table->string('equipments', 200);
            $table->string('device_sn', 100);

            // service info
            $table->string('problems', 200);
            $table->string('actions', 200);
            $table->dateTime('received_datetime')->nullable();
            $table->dateTime('checked_datetime')->nullable();
            $table->dateTime('worked_datetime')->nullable();
            $table->dateTime('completed_datetime')->nullable();
            $table->dateTime('picked_datetime')->nullable();

            // garansi boleh dimulai sejak tanggal selesai, tapi perlu ada field tersendiri
            $table->date('warranty_start_date')->nullable();
            $table->unsignedSmallInteger('warranty_day_count')->default(0); // jumlah hari garansi

            // cost and payment
            $table->decimal('down_payment', 8, 0)->default(0.);
            $table->decimal('estimated_cost', 8, 0)->default(0.);
            $table->decimal('total_cost', 8, 0)->default(0.);

            // extra
            $table->unsignedBigInteger('technician_id')->nullable(true)->default(null);
            $table->text('notes');

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('created_by_uid')->references('id')->on('users')->onDelete('set null');
            $table->foreign('closed_by_uid')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by_uid')->references('id')->on('users')->onDelete('set null');
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
