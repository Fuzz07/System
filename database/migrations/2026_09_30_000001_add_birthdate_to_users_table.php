<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Students give a date of birth instead of typing their age, so the age is
 * always worked out the same way and stays right as birthdays pass. Nullable:
 * accounts made before this have only the old age number until the student
 * fills in their birthdate on the account page.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'birthdate')) {
            Schema::table('users', function (Blueprint $table) {
                $table->date('birthdate')->nullable()->after('age');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'birthdate')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('birthdate');
            });
        }
    }
};
