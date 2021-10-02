<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            
            
            $table->unsignedBigInteger('cart_id')->nullable();
            $table->foreign('cart_id')
                    ->references('id')
                    ->on('carts')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->foreign('transaction_id')
                    ->references('id')
                    ->on('transactions')
                    ->onUpdate('cascade')
                    ->nullOnDelete();
            
            $table->string('vendor_order_id')->index();

            $table->string('billing_email')->nullable();
            $table->string('billing_name')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_province')->nullable();
            $table->string('billing_postalcode')->nullable();
            $table->string('billing_phone')->nullable();
            $table->string('billing_name_on_card')->nullable();
            $table->integer('billing_discount')->default(0);
            $table->string('billing_discount_code')->nullable();
            $table->integer('billing_subtotal');
            $table->integer('billing_total');
            $table->string('payment_gateway')->default('paypal');
            $table->boolean('shipped')->default(false);
            $table->integer('status')->default(0);
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
        Schema::dropIfExists('orders');
    }
}
