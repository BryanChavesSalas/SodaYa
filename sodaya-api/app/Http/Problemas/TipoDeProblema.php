<?php

namespace App\Http\Problemas;

enum TipoDeProblema: string
{
    case SolicitudInvalida = 'solicitud-invalida';
    case NoAutenticado = 'no-autenticado';
    case Prohibido = 'prohibido';
    case NoEncontrado = 'no-encontrado';
    case PedidoNoEncontrado = 'pedido-no-encontrado';
    case MetodoNoPermitido = 'metodo-no-permitido';
    case DatosInvalidos = 'datos-invalidos';
    case PedidoVacio = 'pedido-vacio';
    case CantidadInvalida = 'cantidad-invalida';
    case PlatoNoPerteneceALaSoda = 'plato-no-pertenece-a-la-soda';
    case SodaCerrada = 'soda-cerrada';
    case PlatoNoDisponible = 'plato-no-disponible';
    case PorcionesAgotadas = 'porciones-agotadas';
    case TransicionNoPermitida = 'transicion-no-permitida';
    case PedidoYaPagado = 'pedido-ya-pagado';
    case PuntosInsuficientes = 'puntos-insuficientes';
    case PromocionTraslapada = 'promocion-traslapada';
    case Conflicto = 'conflicto';
    case DemasiadasSolicitudes = 'demasiadas-solicitudes';
    case ErrorInterno = 'error-interno';
    case ServicioNoDisponible = 'servicio-no-disponible';

    /** Código HTTP con que responde este tipo de problema. */
    public function estado(): int
    {
        return match ($this) {
            self::SolicitudInvalida => 400,
            self::NoAutenticado => 401,
            self::Prohibido => 403,
            self::NoEncontrado, self::PedidoNoEncontrado => 404,
            self::MetodoNoPermitido => 405,
            self::DatosInvalidos, self::PedidoVacio, self::CantidadInvalida, self::PlatoNoPerteneceALaSoda => 422,
            self::DemasiadasSolicitudes => 429,
            self::ErrorInterno => 500,
            self::ServicioNoDisponible => 503,
            default => 409,
        };
    }

    /** Resumen legible que no cambia entre ocurrencias. */
    public function titulo(): string
    {
        return match ($this) {
            self::SolicitudInvalida => 'Solicitud inválida',
            self::NoAutenticado => 'No autenticado',
            self::Prohibido => 'Acceso prohibido',
            self::NoEncontrado => 'Recurso no encontrado',
            self::PedidoNoEncontrado => 'Pedido no encontrado',
            self::MetodoNoPermitido => 'Método no permitido',
            self::DatosInvalidos => 'Datos inválidos',
            self::PedidoVacio => 'Pedido vacío',
            self::CantidadInvalida => 'Cantidad inválida',
            self::PlatoNoPerteneceALaSoda => 'Plato de otra soda',
            self::SodaCerrada => 'Soda cerrada',
            self::PlatoNoDisponible => 'Plato no disponible',
            self::PorcionesAgotadas => 'Porciones agotadas',
            self::TransicionNoPermitida => 'Cambio de estado no permitido',
            self::PedidoYaPagado => 'Pedido ya pagado',
            self::PuntosInsuficientes => 'Puntos insuficientes',
            self::PromocionTraslapada => 'Promoción traslapada',
            self::Conflicto => 'Conflicto',
            self::DemasiadasSolicitudes => 'Demasiadas solicitudes',
            self::ErrorInterno => 'Error interno',
            self::ServicioNoDisponible => 'Servicio no disponible',
        };
    }

    /** Explicación fija para los errores que no traen un mensaje escrito para el cliente. */
    public function detalle(): string
    {
        return match ($this) {
            self::SolicitudInvalida => 'La solicitud no se puede procesar tal como viene.',
            self::NoAutenticado => 'Envíe un token válido en el encabezado Authorization.',
            self::Prohibido => 'No tiene permiso para realizar esta acción.',
            self::NoEncontrado => 'El recurso solicitado no existe.',
            self::MetodoNoPermitido => 'Esta ruta no acepta el método HTTP utilizado.',
            self::DatosInvalidos => 'Uno o más campos no cumplen las reglas.',
            self::DemasiadasSolicitudes => 'Superó el límite de solicitudes. Espere antes de intentar de nuevo.',
            self::ErrorInterno => 'Ocurrió un error inesperado. Si se repite, reporte el valor de "instance".',
            self::ServicioNoDisponible => 'Un servicio externo no respondió. Intente de nuevo en unos minutos.',
            default => 'La solicitud choca con el estado actual del recurso.',
        };
    }

    /** URI estable del tipo, igual en todos los ambientes. */
    public function uri(): string
    {
        return rtrim((string) config('sodaya.problemas_uri'), '/').'/'.$this->value;
    }

    /** Tipo genérico de un código HTTP, o null si el código no tiene semántica propia. */
    public static function paraEstado(int $estado): ?self
    {
        return match ($estado) {
            400 => self::SolicitudInvalida,
            401 => self::NoAutenticado,
            403 => self::Prohibido,
            404 => self::NoEncontrado,
            405 => self::MetodoNoPermitido,
            409 => self::Conflicto,
            422 => self::DatosInvalidos,
            429 => self::DemasiadasSolicitudes,
            500 => self::ErrorInterno,
            503 => self::ServicioNoDisponible,
            default => null,
        };
    }

    /**
     * Tipos del catálogo que responden con el código HTTP indicado.
     *
     * @return list<self>
     */
    public static function conEstado(int $estado): array
    {
        return array_values(array_filter(self::cases(), fn (self $tipo): bool => $tipo->estado() === $estado));
    }
}
