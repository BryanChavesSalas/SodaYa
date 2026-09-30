<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Crea la tabla de pedidos con UUID v7 y sin total almacenado. */
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('soda_id')->constrained()->restrictOnDelete();
            $table->foreignId('cliente_id')->constrained('users')->restrictOnDelete();
            $table->string('estado', 20);
            $table->timestamp('realizado_en');
            $table->timestamps();

            $table->index(['soda_id', 'estado']);
            $table->index(['cliente_id', 'realizado_en']);
        });

        DB::statement("ALTER TABLE pedidos ADD CONSTRAINT pedidos_estado_valido CHECK (estado IN ('pendiente', 'confirmado', 'listo', 'entregado', 'cancelado'))");
    }

    /** Elimina la tabla de pedidos. */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
