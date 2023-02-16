<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order', function (Blueprint $table) {
            $table->bigIncrements('order_id', 11);
            $table->string('order_number');
            $table->string('customer_name', 100);
            $table->string('customer_email', 100);
            $table->timestamp('order_date');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->string('guest_name');
            $table->integer('room_qty');
            $table->integer('total')->nullable();
            $table->unsignedBigInteger('room_type_id');
            $table->enum('order_status', ['New', 'Check In', 'Check Out']);
            $table->unsignedBigInteger('user_id')->nullable();

            $table->foreign('room_type_id')->references('room_type_id')->on('room_type');
            $table->foreign('user_id')->references('user_id')->on('user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order');
    }
}
