<?php

declare(strict_types=1);

namespace SodaYa\Compartido\Domain;

use DomainException;

abstract class ErrorDeDominio extends DomainException
{
    /** Código estable del catálogo de la ERS que identifica la regla incumplida. */
    abstract public function codigo(): string;

    /**
     * Datos adicionales que se pueden mostrar a quien consume la API.
     *
     * @return array<string, int|string|bool>
     */
    public function contexto(): array
    {
        return [];
    }
}
