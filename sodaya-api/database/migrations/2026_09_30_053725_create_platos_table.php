<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Crea la tabla de platos con sus rangos como restricciones CHECK. */
    public function up(): void
    {
        Schema::create('platos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('soda_id')->constrained()->cascadeOnDelete();
            $table->string('nombre', 120);
            $table->integer('precio');
            $table->smallInteger('minutos_preparacion');
            $table->integer('porciones_disponibles')->default(0);
            $table->boolean('disponible')->default(true);
            $table->timestamps();

            $table->unique(['soda_id', 'nombre']);
            $table->index(['soda_id', 'disponible']);
        });

        DB::statement('ALTER TABLE platos ADD CONSTRAINT platos_precio_100_a_100000 CHECK (precio BETWEEN 100 AND 100000)');
        DB::statement('ALTER TABLE platos ADD CONSTRAINT platos_minutos_1_a_120 CHECK (minutos_preparacion BETWEEN 1 AND 120)');
        DB::statement('ALTER TABLE platos ADD CONSTRAINT platos_porciones_no_negativas CHECK (porciones_disponibles >= 0)');
    }

    /** Elimina la tabla de platos. */
    public function down(): void
    {
        Schema::dropIfExists('platos');
    }
};
