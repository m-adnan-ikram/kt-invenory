<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreIssuanceNotesDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_issuance_note_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_issuance_note_id')
                ->constrained('store_issuance_notes')
                ->onDelete('cascade');
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade');
            $table->integer('qty');
            $table->decimal('rate', 10, 2); 
            $table->decimal('total', 10, 2);
            $table->string('company_id');

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
        Schema::dropIfExists('store_issuance_notes_details');
    }
}
