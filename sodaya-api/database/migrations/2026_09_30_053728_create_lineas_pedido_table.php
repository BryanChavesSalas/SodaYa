<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Crea las líneas del pedido con el nombre y el precio congelados. */
    public function up(): void
    {
        Schema::create('lineas_pedido', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('pedido_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plato_id')->constrained()->restrictOnDelete();
            $table->string('nombre_plato', 120);
            $table->integer('precio_unitario');
            $table->smallInteger('cantidad');

            $table->unique(['pedido_id', 'plato_id']);
            $table->index('plato_id');
        });

        DB::statement('ALTER TABLE lineas_pedido ADD CONSTRAINT lineas_precio_positivo CHECK (precio_unitario > 0)');
        DB::statement('ALTER TABLE lineas_pedido ADD CONSTRAINT lineas_cantidad_1_a_20 CHECK (cantidad BETWEEN 1 AND 20)');
    }

    /** Elimina las líneas del pedido. */
    public function down(): void
    {
        Schema::dropIfExists('lineas_pedido');
    }
};
