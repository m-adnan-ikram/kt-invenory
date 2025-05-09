<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnGoodReceiveNoteItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
          // 1A. Return Good Receive Note Items (Details)
          Schema::create('return_good_receive_note_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('return_good_receive_note_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('qty');
            $table->timestamps();

            $table->foreign('return_good_receive_note_id', 'rgrn_fk')
                ->references('id')->on('return_good_receive_notes')->onDelete('cascade');

            $table->foreign('product_id', 'rgrn_product_fk')
                ->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('return_good_receive_note_items');
    }
}
