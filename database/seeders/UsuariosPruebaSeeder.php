<?php

namespace Database\Seeders;

use App\Models\CategoriaNegocio;
use App\Models\Negocio;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class UsuariosPruebaSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['administrador', 'cliente', 'negocio', 'repartidor'] as $rolNombre) {
            Usuario::updateOrCreate(
                ['correo' => "{$rolNombre}@delivery.test"],
                [
                    'rol_id' => Rol::where('nombre', $rolNombre)->value('id'),
                    'nombres' => ucfirst($rolNombre),
                    'apellidos' => 'Prueba',
                    'telefono' => match ($rolNombre) {
                        'administrador' => '70000001',
                        'cliente' => '70000002',
                        'negocio' => '70000003',
                        default => '70000004',
                    },
                    'password' => 'Desarrollo123!',
                    'activo' => true,
                ],
            );
        }

        $usuarioNegocio = Usuario::where('correo', 'negocio@delivery.test')->firstOrFail();
        Negocio::updateOrCreate(
            ['usuario_id' => $usuarioNegocio->id, 'nombre' => 'Negocio de prueba'],
            [
                'categoria_negocio_id' => CategoriaNegocio::where('nombre', 'Tienda')->value('id'),
                'telefono' => $usuarioNegocio->telefono,
                'direccion_referencia' => 'Centro de Patacamaya',
                'estado' => 'aprobado',
                'activo' => true,
            ],
        );
    }
}
