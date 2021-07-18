<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100);
            $table->integer('reversed_approved_id')->nullable();
            $table->integer('generated_by_user_id')->nullable();
            $table->integer('customer_id');
            $table->integer('store_id');
            $table->enum('reversed_status', ['Pending', 'Denied', 'successful'])->nullable();
            $table->boolean('reversed')->default(false);
            $table->string('total_amount')->default('0');
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
        Schema::dropIfExists('invoices');
    }
}
