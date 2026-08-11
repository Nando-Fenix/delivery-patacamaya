<?php

namespace App\Enums;

enum EstadoPedido: string
{
    case Pendiente = 'pendiente';
    case Aceptado = 'aceptado';
    case Rechazado = 'rechazado';
    case EnPreparacion = 'en_preparacion';
    case Listo = 'listo';
    case EnCamino = 'en_camino';
    case Entregado = 'entregado';
    case Cancelado = 'cancelado';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Aceptado => 'Aceptado',
            self::Rechazado => 'Rechazado',
            self::EnPreparacion => 'En preparación',
            self::Listo => 'Listo',
            self::EnCamino => 'En camino',
            self::Entregado => 'Entregado',
            self::Cancelado => 'Cancelado',
        };
    }

    public function siguientesNegocio(): array
    {
        return match ($this) {
            self::Pendiente => [self::Aceptado, self::Rechazado],
            self::Aceptado => [self::EnPreparacion],
            self::EnPreparacion => [self::Listo],
            default => [],
        };
    }
}
