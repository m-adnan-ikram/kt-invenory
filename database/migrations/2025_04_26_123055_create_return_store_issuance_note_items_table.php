<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnStoreIssuanceNoteItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_store_issuance_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_store_issuance_note_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->integer('qty');
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
        Schema::dropIfExists('return_store_issuance_note_items');
    }
}
