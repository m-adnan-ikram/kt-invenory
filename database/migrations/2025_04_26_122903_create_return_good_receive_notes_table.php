<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnGoodReceiveNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_good_receive_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('good_receive_note_id')->constrained();
            $table->dateTime('return_date');
            $table->text('reason')->nullable();
            $table->string('handled_by');
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
        Schema::dropIfExists('return_good_receive_notes');
    }
}
