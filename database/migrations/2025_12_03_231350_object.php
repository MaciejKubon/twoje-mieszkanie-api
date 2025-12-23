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
        Schema::create('objects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_owner')->constrained('users', 'id');
            $table->enum('type_of_building',['house','apartment','room'])->default('apartment');
            $table->string('country')->nullable();
            $table->string('voivodeship')->nullable();
            $table->string('city')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('street')->nullable();
            $table->string('house_number')->nullable();
            $table->string('apartment_number')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('objects', function (Blueprint $table) {
        $table->dropSoftDeletes();
    });
    }
};
