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
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre',100);//acepta no nulo
            $table->text('descripcion')->nullable();//acepta null
            $table->decimal('precio', 10, 2)->default(0);
            $table->integer('duracion_estimada')->default(40);//40 min por defecto
            $table->string('estado',50)->default('activo');
            $table->date('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};
