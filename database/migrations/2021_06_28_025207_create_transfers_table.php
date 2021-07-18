<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('ref_code');
            $table->integer('to');
            $table->integer('from');
            $table->integer('approved_by_id')->nullable();
            $table->enum('transfer_type', ['STORE_TO_STORE', 'STORE_TO_WAREHOUSE','WAREHOUSE_TO_STORE', 'WAREHOUSE_TO_WAREHOUSE', ])
            ->comment('first word before ( _ ) indicates where the product is coming from)');
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
        Schema::dropIfExists('transfers');
    }
}
