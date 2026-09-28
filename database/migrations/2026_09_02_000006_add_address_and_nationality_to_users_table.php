<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nationality_type')->default('local')->after('phone'); // local | international
            $table->string('passport_number')->nullable()->after('nationality_type');
            $table->string('address_line_1')->nullable()->after('passport_number');
            $table->string('address_line_2')->nullable()->after('address_line_1');
            $table->string('postcode', 20)->nullable()->after('address_line_2');
            $table->string('city', 100)->nullable()->after('postcode');
            $table->string('state', 100)->nullable()->after('city');
            $table->string('country', 100)->default('Malaysia')->after('state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nationality_type',
                'passport_number',
                'address_line_1',
                'address_line_2',
                'postcode',
                'city',
                'state',
                'country',
            ]);
        });
    }
};
