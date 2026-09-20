<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollment_payments', function (Blueprint $table) {
            $table->string('paymongo_checkout_session_id')->nullable()->unique()->after('reference');
            $table->string('paymongo_payment_id')->nullable()->unique()->after('paymongo_checkout_session_id');
            $table->string('paymongo_payment_method')->nullable()->after('paymongo_payment_id');
        });
    }

    public function down(): void
    {
        Schema::table('enrollment_payments', function (Blueprint $table) {
            $table->dropUnique(['paymongo_checkout_session_id']);
            $table->dropUnique(['paymongo_payment_id']);
            $table->dropColumn([
                'paymongo_checkout_session_id',
                'paymongo_payment_id',
                'paymongo_payment_method',
            ]);
        });
    }
};
