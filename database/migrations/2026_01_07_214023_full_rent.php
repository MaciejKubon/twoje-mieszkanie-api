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
        Schema::create('full_rent', function (Blueprint $table) {
           $table->id();
           $table->foreignId('id_rent_assigment')->nullable()->constrained('rent_assigment', 'id');
           $table->float('amount');
           $table->date('date');
           $table->boolean('is_accepted')->default(false);
           $table->boolean('is_paid')->default(false);
           $table->date('date_paid')->nullable();
           $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('full_rent');
    }
};
