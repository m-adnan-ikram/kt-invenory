<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodReceiveNotesDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('good_receive_notes_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('good_receive_note_id')->constrained('good_receive_notes');
            $table->foreignId('product_id')->constrained('products');
            $table->integer('qty');
            $table->decimal('rate', 10, 2);
            $table->decimal('total', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('delivery_charges', 10, 2);
            $table->decimal('discount', 10, 2);
            $table->decimal('net_amount', 10, 2);
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
        Schema::dropIfExists('good_receive_notes_details');
    }
}
