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
        Schema::create('rent_assigment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_renter')->nullable()->constrained('users', 'id');
            $table->foreignId('id_object')->constrained('objects', 'id');
            $table->boolean('confirmed')->default(false);
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rent_assignment', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
