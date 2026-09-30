<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Crea la tabla de sodas, el inquilino del que dependen las demás tablas. */
    public function up(): void
    {
        Schema::create('sodas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('telefono', 20)->nullable();
            $table->timestamps();
        });
    }

    /** Elimina la tabla de sodas. */
    public function down(): void
    {
        Schema::dropIfExists('sodas');
    }
};
