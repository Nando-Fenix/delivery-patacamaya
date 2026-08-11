<?php

namespace Database\Seeders;

use App\Models\CategoriaNegocio;
use Illuminate\Database\Seeder;

class CategoriasNegocioSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Restaurante', 'Librería', 'Ferretería', 'Tienda'] as $nombre) {
            CategoriaNegocio::updateOrCreate(['nombre' => $nombre], ['activo' => true]);
        }
    }
}
