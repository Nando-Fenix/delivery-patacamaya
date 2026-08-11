<?php

namespace Tests\Feature;

use App\Models\CategoriaNegocio;
use App\Models\DireccionUsuario;
use App\Models\Negocio;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\Zona;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UbicacionesZonasTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $admin;

    private Usuario $cliente;

    private Usuario $propietario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = Usuario::where('correo', 'administrador@delivery.test')->firstOrFail();
        $this->cliente = Usuario::where('correo', 'cliente@delivery.test')->firstOrFail();
        $this->propietario = Usuario::where('correo', 'negocio@delivery.test')->firstOrFail();
    }

    public function test_administrador_lista_crea_edita_y_desactiva_zonas(): void
    {
        $this->actingAs($this->admin)->get(route('administrador.zonas.index'))->assertOk();
        $this->post(route('administrador.zonas.store'), ['nombre' => 'Central', 'activo' => 1])->assertRedirect();
        $z = Zona::firstOrFail();
        $this->put(route('administrador.zonas.update', $z), ['nombre' => 'Centro', 'activo' => 1])->assertRedirect();
        $this->patch(route('administrador.zonas.estado', $z))->assertRedirect();
        $this->assertDatabaseHas('zonas', ['id' => $z->id, 'nombre' => 'Centro', 'activo' => false]);
    }

    public function test_no_administrador_no_gestiona_zonas(): void
    {
        $this->actingAs($this->cliente)->get(route('administrador.zonas.index'))->assertForbidden();
    }

    public function test_cliente_crea_y_edita_direccion(): void
    {
        $z = Zona::create(['nombre' => 'Norte']);
        $data = $this->datos($z);
        $this->actingAs($this->cliente)->post(route('cliente.direcciones.store'), $data)->assertRedirect();
        $d = DireccionUsuario::firstOrFail();
        $data['nombre'] = 'Trabajo';
        $this->put(route('cliente.direcciones.update', $d), $data)->assertRedirect();
        $this->assertDatabaseHas('direcciones_usuario', ['id' => $d->id, 'nombre' => 'Trabajo']);
    }

    public function test_estados_vacios_y_selector_de_zonas_activas(): void
    {
        Zona::query()->delete();
        $this->actingAs($this->cliente)->get(route('cliente.direcciones.create'))
            ->assertOk()->assertSee('No existen zonas de entrega disponibles actualmente.')
            ->assertDontSee('name="zona_id"', false)->assertSee('disabled', false);
        $this->actingAs($this->admin)->get(route('administrador.zonas.index'))
            ->assertOk()->assertSee('Crear primera zona');

        Zona::create(['nombre' => 'Activa visible', 'activo' => true]);
        Zona::create(['nombre' => 'Inactiva oculta', 'activo' => false]);
        $this->actingAs($this->cliente)->get(route('cliente.direcciones.create'))
            ->assertSee('Activa visible')->assertDontSee('Inactiva oculta');
    }

    public function test_desactiva_y_marca_una_sola_predeterminada(): void
    {
        $z = Zona::create(['nombre' => 'Sur']);
        $a = $this->cliente->direcciones()->create($this->datos($z) + ['activo' => true]);
        $b = $this->cliente->direcciones()->create($this->datos($z) + ['nombre' => 'Trabajo', 'activo' => true]);
        $this->actingAs($this->cliente)->patch(route('cliente.direcciones.predeterminada', $a));
        $this->patch(route('cliente.direcciones.predeterminada', $b));
        $this->assertDatabaseHas('direcciones_usuario', ['id' => $a->id, 'predeterminada' => false]);
        $this->assertDatabaseHas('direcciones_usuario', ['id' => $b->id, 'predeterminada' => true]);
        $this->patch(route('cliente.direcciones.estado', $b));
        $this->assertDatabaseHas('direcciones_usuario', ['id' => $b->id, 'activo' => false, 'predeterminada' => false]);
    }

    public function test_cliente_no_modifica_direccion_ajena(): void
    {
        $otro = Usuario::create(['rol_id' => Rol::where('nombre', 'cliente')->value('id'), 'nombres' => 'Otro', 'apellidos' => 'Cliente', 'telefono' => '79999991', 'correo' => 'otro@test.com', 'password' => 'password', 'activo' => true]);
        $z = Zona::create(['nombre' => 'Este']);
        $d = $otro->direcciones()->create($this->datos($z) + ['activo' => true]);
        $this->actingAs($this->cliente)->get(route('cliente.direcciones.edit', $d))->assertForbidden();
        $this->put(route('cliente.direcciones.update', $d), $this->datos($z))->assertForbidden();
    }

    public function test_rechaza_coordenadas_y_zona_invalidas(): void
    {
        $z = Zona::create(['nombre' => 'Oeste', 'activo' => false]);
        $this->actingAs($this->cliente)->post(route('cliente.direcciones.store'), array_merge($this->datos($z), ['latitud' => 91, 'longitud' => 181]))->assertSessionHasErrors(['zona_id', 'latitud', 'longitud']);
    }

    public function test_negocio_guarda_ubicacion_propia_y_no_ajena(): void
    {
        $z = Zona::create(['nombre' => 'Central']);
        $n = $this->propietario->negocios()->firstOrFail();
        $this->actingAs($this->propietario)->put(route('negocio.ubicacion.update', $n), ['zona_id' => $z->id, 'direccion_referencia' => 'Plaza', 'latitud' => -17.23, 'longitud' => -67.92])->assertRedirect();
        $this->assertDatabaseHas('negocios', ['id' => $n->id, 'zona_id' => $z->id]);
        $otro = Usuario::create(['rol_id' => Rol::where('nombre', 'negocio')->value('id'), 'nombres' => 'Otro', 'apellidos' => 'Negocio', 'telefono' => '79999992', 'correo' => 'otro-neg@test.com', 'password' => 'password', 'activo' => true]);
        $ajeno = Negocio::create(['usuario_id' => $otro->id, 'categoria_negocio_id' => CategoriaNegocio::first()->id, 'nombre' => 'Ajeno', 'telefono' => '79999992']);
        $this->put(route('negocio.ubicacion.update', $ajeno), ['zona_id' => $z->id, 'direccion_referencia' => 'X', 'latitud' => -17, 'longitud' => -67])->assertForbidden();
    }

    private function datos(Zona $z): array
    {
        return ['zona_id' => $z->id, 'nombre' => 'Casa', 'direccion_referencia' => 'Puerta azul', 'latitud' => -17.235, 'longitud' => -67.921, 'predeterminada' => 1];
    }
}
