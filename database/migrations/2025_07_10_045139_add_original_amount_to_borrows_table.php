<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->unsignedBigInteger('original_amount')->after('amount')->nullable();
            $table->unsignedBigInteger('remaining_amount')->after('original_amount')->nullable();
        });

        // Initialize original_amount and remaining_amount with current amount value
        DB::table('borrows')->update([
            'original_amount' => DB::raw('amount'),
            'remaining_amount' => DB::raw('amount'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->dropColumn('original_amount');
            $table->dropColumn('remaining_amount');
        });
    }
};
