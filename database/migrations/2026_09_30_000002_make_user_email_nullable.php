<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Students the admin adds from the school roster (by hand or CSV import) have
 * no email yet. They claim the record by registering with the same ID number,
 * which fills in their Gmail and password. Email stays unique; several empty
 * ones are allowed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Fails while unclaimed roster students (no email) still exist.
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }
};
