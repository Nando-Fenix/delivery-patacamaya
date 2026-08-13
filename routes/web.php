<?php

use App\Http\Controllers\Admin\CategoriaNegocioController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NegocioController;
use App\Http\Controllers\Admin\SubcategoriaNegocioController;
use App\Http\Controllers\Admin\ZonaController;
use App\Http\Controllers\AutenticacionController;
use App\Http\Controllers\Cliente\CarritoController;
use App\Http\Controllers\Cliente\CheckoutController;
use App\Http\Controllers\Cliente\DireccionController;
use App\Http\Controllers\Cliente\ExplorarController;
use App\Http\Controllers\Cliente\PedidoController as ClientePedidoController;
use App\Http\Controllers\Negocio\CategoriaProductoController as NegocioCategoriaProductoController;
use App\Http\Controllers\Negocio\DashboardController as NegocioDashboardController;
use App\Http\Controllers\Negocio\HorarioController;
use App\Http\Controllers\Negocio\MiNegocioController;
use App\Http\Controllers\Negocio\PedidoController as NegocioPedidoController;
use App\Http\Controllers\Negocio\ProductoController as NegocioProductoController;
use App\Http\Controllers\Negocio\UbicacionController;
use App\Http\Controllers\Repartidor\EntregaController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AutenticacionController::class, 'mostrarLogin'])->name('login');
    Route::post('/login', [AutenticacionController::class, 'login'])->name('login.autenticar');
});

Route::post('/logout', [AutenticacionController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'rol:administrador'])
    ->prefix('admin')
    ->name('administrador.')
    ->group(function () {
        Route::get('/inicio', DashboardController::class)->name('inicio');
        Route::resource('categorias', CategoriaNegocioController::class)
            ->parameters(['categorias' => 'categoria'])
            ->except(['show', 'destroy']);
        Route::patch('/categorias/{categoria}/estado', [CategoriaNegocioController::class, 'cambiarEstado'])
            ->name('categorias.estado');

        Route::resource('subcategorias', SubcategoriaNegocioController::class)
            ->parameters(['subcategorias' => 'subcategoria'])
            ->except(['show', 'destroy']);
        Route::patch('/subcategorias/{subcategoria}/estado', [SubcategoriaNegocioController::class, 'cambiarEstado'])->name('subcategorias.estado');

        Route::resource('zonas', ZonaController::class)->except(['show', 'destroy']);
        Route::patch('/zonas/{zona}/estado', [ZonaController::class, 'estado'])->name('zonas.estado');

        Route::get('/negocios', [NegocioController::class, 'index'])->name('negocios.index');
        Route::get('/negocios/{negocio}', [NegocioController::class, 'show'])->name('negocios.show');
        Route::get('/negocios/{negocio}/clasificacion', [NegocioController::class, 'editarClasificacion'])->name('negocios.clasificacion.edit');
        Route::put('/negocios/{negocio}/clasificacion', [NegocioController::class, 'actualizarClasificacion'])->name('negocios.clasificacion.update');
        Route::patch('/negocios/{negocio}/estado', [NegocioController::class, 'cambiarEstado'])->name('negocios.estado');
        Route::patch('/negocios/{negocio}/activo', [NegocioController::class, 'cambiarActivo'])->name('negocios.activo');
    });

Route::middleware(['auth', 'rol:negocio'])->prefix('negocio')->name('negocio.')->group(function () {
    Route::get('/inicio', [NegocioDashboardController::class, 'index'])->name('inicio');
    Route::post('/seleccionar/{negocio}', [NegocioDashboardController::class, 'seleccionar'])->name('seleccionar');
    Route::middleware('negocio.propietario')->prefix('/negocios/{negocio}')->group(function () {
        Route::get('/editar', [MiNegocioController::class, 'edit'])->name('mi-negocio.edit');
        Route::put('/', [MiNegocioController::class, 'update'])->name('mi-negocio.update');
        Route::put('/subcategorias', [MiNegocioController::class, 'actualizarSubcategorias'])->name('mi-negocio.subcategorias.update');
        Route::get('/ubicacion', [UbicacionController::class, 'edit'])->name('ubicacion.edit');
        Route::put('/ubicacion', [UbicacionController::class, 'update'])->name('ubicacion.update');
        Route::get('/horarios', [HorarioController::class, 'edit'])->name('horarios.edit');
        Route::put('/horarios', [HorarioController::class, 'update'])->name('horarios.update');
        Route::resource('categorias-producto', NegocioCategoriaProductoController::class)->parameters(['categorias-producto' => 'categoriaProducto'])->except(['show', 'destroy']);
        Route::patch('/categorias-producto/{categoriaProducto}/estado', [NegocioCategoriaProductoController::class, 'estado'])->name('categorias-producto.estado');
        Route::resource('productos', NegocioProductoController::class)->except(['show', 'destroy']);
        Route::patch('/productos/{producto}/estado', [NegocioProductoController::class, 'estado'])->name('productos.estado');
        Route::patch('/productos/{producto}/disponibilidad', [NegocioProductoController::class, 'disponibilidad'])->name('productos.disponibilidad');
        Route::get('/pedidos', [NegocioPedidoController::class, 'index'])->name('pedidos.index');
        Route::get('/pedidos/{pedido}', [NegocioPedidoController::class, 'show'])->name('pedidos.show');
        Route::patch('/pedidos/{pedido}/estado', [NegocioPedidoController::class, 'estado'])->name('pedidos.estado');
    });
});

Route::middleware(['auth', 'rol:cliente'])->prefix('cliente')->name('cliente.')->group(function () {
    Route::get('/inicio', [ExplorarController::class, 'index'])->name('inicio');
    Route::get('/buscar', [ExplorarController::class, 'index'])->name('buscar');
    Route::get('/negocios/{negocio}', [ExplorarController::class, 'show'])->name('negocios.show');
    Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito.index');
    Route::post('/carrito/productos/{producto}', [CarritoController::class, 'store'])->name('carrito.store');
    Route::put('/carrito/items/{item}', [CarritoController::class, 'update'])->name('carrito.update');
    Route::delete('/carrito/items/{item}', [CarritoController::class, 'destroy'])->name('carrito.destroy');
    Route::delete('/carrito', [CarritoController::class, 'vaciar'])->name('carrito.vaciar');
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/pedidos', [ClientePedidoController::class, 'index'])->name('pedidos.index');
    Route::get('/pedidos/{pedido}', [ClientePedidoController::class, 'show'])->name('pedidos.show');
    Route::patch('/pedidos/{pedido}/cancelar', [ClientePedidoController::class, 'cancelar'])->name('pedidos.cancelar');
    Route::get('/perfil', [ExplorarController::class, 'perfil'])->name('perfil');
    Route::resource('direcciones', DireccionController::class)->parameters(['direcciones' => 'direccion'])->except(['show', 'destroy']);
    Route::patch('/direcciones/{direccion}/estado', [DireccionController::class, 'estado'])->name('direcciones.estado');
    Route::patch('/direcciones/{direccion}/predeterminada', [DireccionController::class, 'predeterminada'])->name('direcciones.predeterminada');
});
Route::middleware(['auth', 'rol:repartidor'])->prefix('repartidor')->name('repartidor.')->group(function () {
    Route::redirect('/inicio', '/repartidor/entregas/disponibles')->name('inicio');
    Route::get('/entregas/disponibles', [EntregaController::class, 'disponibles'])->name('entregas.disponibles');
    Route::get('/entregas', [EntregaController::class, 'propias'])->name('entregas.propias');
    Route::post('/entregas/{pedido}/aceptar', [EntregaController::class, 'aceptar'])->name('entregas.aceptar');
    Route::get('/entregas/{pedido}', [EntregaController::class, 'show'])->name('entregas.show');
    Route::patch('/entregas/{pedido}/iniciar', [EntregaController::class, 'iniciar'])->name('entregas.iniciar');
    Route::patch('/entregas/{pedido}/entregar', [EntregaController::class, 'entregar'])->name('entregas.entregar');
    Route::post('/entregas/{pedido}/ubicacion', [EntregaController::class, 'ubicacion'])->name('entregas.ubicacion');
});
