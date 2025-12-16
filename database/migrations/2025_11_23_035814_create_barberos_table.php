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
        Schema::create('barberos', function (Blueprint $table) {
            $table->unsignedBigInteger('id'); 
            $table->primary('id'); 
            $table->foreign('id')
                  ->references('id') 
                  ->on('users')
                  ->onDelete('cascade');
            $table->enum('estado_barbero', ['disponible', 'ocupado', 'descanso'])->default('disponible');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barberos');
    }
};
