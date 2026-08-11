<?php

namespace Database\Factories;

use App\Models\Rol;
use Illuminate\Database\Eloquent\Factories\Factory;

class UsuarioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'rol_id' => fn () => Rol::firstOrCreate(['nombre' => 'cliente'])->id,
            'nombres' => fake()->firstName(),
            'apellidos' => fake()->lastName(),
            'telefono' => fake()->unique()->numerify('7#######'),
            'correo' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'activo' => true,
        ];
    }
}
