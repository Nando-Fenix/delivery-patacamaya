<?php

namespace Database\Seeders;

use App\Models\CategoriaNegocio;
use App\Models\SubcategoriaNegocio;
use Illuminate\Database\Seeder;

class SubcategoriasNegocioSeeder extends Seeder
{
    public function run(): void
    {
        $datos = [
            'Restaurante' => ['Pollería', 'Pizzería', 'Comida rápida', 'Comida tradicional'],
            'Librería' => ['Material escolar', 'Papelería', 'Libros'],
            'Ferretería' => ['Herramientas', 'Material eléctrico', 'Pinturas'],
            'Tienda' => ['Abarrotes', 'Ropa', 'Variedades'],
        ];

        foreach ($datos as $categoriaNombre => $nombres) {
            $categoria = CategoriaNegocio::where('nombre', $categoriaNombre)->first();
            if (! $categoria) {
                continue;
            }
            foreach ($nombres as $nombre) {
                SubcategoriaNegocio::firstOrCreate(
                    ['categoria_negocio_id' => $categoria->id, 'nombre' => $nombre],
                    ['activo' => true],
                );
            }
        }
    }
}
