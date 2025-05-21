<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnStoreIssuanceNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_store_issuance_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_issuance_note_id')->constrained();
            $table->dateTime('return_date');
            $table->text('reason')->nullable();
            $table->string('returned_by');
            $table->string('approved_by')->nullable();
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
        Schema::dropIfExists('return_store_issuance_notes');
    }
}
