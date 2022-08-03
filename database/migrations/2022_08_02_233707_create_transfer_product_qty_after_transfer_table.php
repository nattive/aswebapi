<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferProductQtyAfterTransferTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transfer_products', function (Blueprint $table) {
            $table->string('from_qty_after_transfer')->default(0);
            $table->string('to_qty_after_transfer')->default(0);
            $table->string('status')->default('TRANSFERRED')->comment('TRANSFERRED, PARTIALLY_TRANSFERRED, NOT_TRANSFERRED');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transfer_products', function (Blueprint $table) {
            //
        });
    }
}
