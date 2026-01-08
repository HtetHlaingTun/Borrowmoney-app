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
        Schema::create('show_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('borrow_id');
            $table->foreignId('currency_id');
            $table->integer('amount');
            $table->decimal('rate', 8, 2);
            $table->decimal('interest', 12, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('days');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('show_interests');
    }
};
