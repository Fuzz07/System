<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('enrollment_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 8, 2)->default(50.00);
            $table->string('semester')->nullable();
            $table->string('method')->default('gcash');
            $table->string('status')->default('pending'); // pending, paid
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('admin_marked_by')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('enrollment_payments');
    }
};
