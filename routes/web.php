<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ReporteExportController;
use App\Http\Controllers\CotizacionDolarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CajaController;

Route::resource('caja', CajaController::class);



Route::post('/productos/store-ajax', [App\Http\Controllers\ProductoController::class, 'storeAjax'])->name('productos.storeAjax');


Route::resource('vendedores', App\Http\Controllers\VendedorController::class)->parameters([
    'vendedores' => 'vendedor'
]);


Route::delete('/compras/detalle/{id}', [CompraController::class, 'removeItem'])->name('compras.removeItem');

Route::post('/categorias/store-ajax', [App\Http\Controllers\CategoriaController::class, 'storeAjax'])->name('categorias.storeAjax');

Route::resource('categorias', CategoriaController::class);

Route::post('/productos/crear-rapido', [App\Http\Controllers\ProductoController::class, 'storeAjax'])
    ->name('productos.storeAjax');



// =====================
// 🔹 RUTAS DE COTIZACION
// =====================
Route::get('/cotizacion', [CotizacionDolarController::class, 'index'])->name('cotizacion.index');
Route::post('/cotizacion', [CotizacionDolarController::class, 'store'])->name('cotizacion.store');

// =====================
// 🔹 RUTAS DE REPORTES
// =====================
Route::prefix('reportes')->group(function () {
    // Dashboard principal con filtros
    Route::get('/', [ReporteController::class, 'index'])->name('reportes.index');

    // Exportaciones PDF y Excel
    Route::get('/export/pdf', [ReporteExportController::class, 'exportPdf'])->name('reportes.export.pdf');
    Route::get('/export/excel', [ReporteExportController::class, 'exportExcel'])->name('reportes.export.excel');
});

// =====================
// 🔹 CRUDs PRINCIPALES
// =====================
Route::resource('ventas', VentaController::class);
Route::resource('compras', CompraController::class);
Route::resource('productos', ProductoController::class);

// =====================
// 🔹 PÁGINAS BASE
// =====================
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


// =====================
// 🔹 PERFIL DE USUARIO
// =====================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
