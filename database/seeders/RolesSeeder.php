<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['administrador', 'cliente', 'negocio', 'repartidor'] as $nombre) {
            Rol::updateOrCreate(['nombre' => $nombre], ['descripcion' => ucfirst($nombre)]);
        }
    }
}
