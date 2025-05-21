<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bid_id')->constrained('bid_summaries');
            $table->foreignId('mr_id')->constrained('material_requests');
            $table->foreignId('prn_id')->constrained('purchase_requisition_notes');
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->decimal('total', 10, 2);
            $table->decimal('remaining', 10, 2);
            $table->string('status'); // 0= Rejected,  1 = Processing,  2 = Generated
            $table->string('added_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_orders');
    }
}
