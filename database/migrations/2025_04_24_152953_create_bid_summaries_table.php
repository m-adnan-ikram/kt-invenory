<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBidSummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bid_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prn_id')->constrained('purchase_requisition_notes');
            $table->foreignId('mr_id')->constrained('material_requests');
            $table->string('status'); // 0= Rejected,  1 = Processing,  2 = Generated
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('total', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('advance', 10, 2);
            $table->decimal('advance_amount', 10, 2);
            $table->decimal('after_delivery', 10, 2);
            $table->decimal('after_delivery_amount', 10, 2);
            $table->integer('credit_days');
            $table->decimal('discount', 10, 2);
            $table->decimal('delivery_charges', 10, 2);
            $table->string('contact_person');
            $table->string('contact_person_contact');
            $table->text('terms_condition');
            $table->string('quotation_date'); 
            $table->string('quotation_ref');  
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
        Schema::dropIfExists('bid_summaries');
    }
}
