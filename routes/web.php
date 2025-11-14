<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CarterasController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\VodafoneController;

//------------------
use App\Http\Controllers\ChipController;
use App\Http\Controllers\CompaniaController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\RegistroController;
use Inertia\Inertia;


//------------------
use App\Http\Controllers\WahaConexionController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\CampaniaController;

// ===================
// RUTA INICIAL
// ===================
Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
});

// ===================
// AUTENTICACIÓN
// ===================
require __DIR__ . '/auth.php';

// ===================
// RUTAS PROTEGIDAS
// ===================
Route::middleware('auth')->group(function () {

    // PERFIL Y DASHBOARD
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/reporte/{id}', [DashboardController::class, 'showReporte'])->name('dashboard.reporte');


    // ===================
    // ROLES
    // ===================
    Route::middleware('can:roles.ver')->get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::middleware('can:roles.crear')->post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::middleware('can:roles.editar')->put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::middleware('can:roles.eliminar')->delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    // ===================
    // USUARIOS
    // ===================
    Route::middleware('can:usuarios.ver')->get('/users', [UserController::class, 'index'])->name('users.index');
    Route::middleware('can:usuarios.crear')->post('/users', [UserController::class, 'store'])->name('users.store');
    Route::middleware('can:usuarios.editar')->put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::middleware('can:usuarios.eliminar')->delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // ===================
    // CARTERAS
    // ===================
    Route::middleware('can:carteras.ver')->get('/carteras', [CarterasController::class, 'index'])->name('carteras.index');
    Route::middleware('can:carteras.crear')->post('/carteras', [CarterasController::class, 'store'])->name('carteras.store');
    Route::middleware('can:carteras.editar')->put('/carteras/{cartera}', [CarterasController::class, 'update'])->name('carteras.update');
    Route::middleware('can:carteras.eliminar')->delete('/carteras/{cartera}', [CarterasController::class, 'destroy'])->name('carteras.destroy');

    // ===================
    // REPORTES
    // ===================
    Route::middleware('can:reportes.ver')->get('/reportes', [ReportesController::class, 'index'])->name('reportes.index');
    Route::middleware('can:reportes.crear')->post('/reportes', [ReportesController::class, 'store'])->name('reportes.store');
    Route::middleware('can:reportes.editar')->put('/reportes/{reporte}', [ReportesController::class, 'update'])->name('reportes.update');
    Route::middleware('can:reportes.eliminar')->delete('/reportes/{reporte}', [ReportesController::class, 'destroy'])->name('reportes.destroy');

    // ===================
    // MÓDULOS (desde pestaña Roles)
    // ===================
    Route::middleware('can:roles.crear')->post('/modulos', [ModuleController::class, 'store'])->name('modules.store');

    // ===================
    // VODAFONE
    // ===================
    Route::middleware('can:vodafone.ver')->get('/vodafone', [VodafoneController::class, 'index'])->name('vodafone.index');
    Route::middleware('can:vodafone.guardar')->post('/vodafone', [VodafoneController::class, 'store'])->name('vodafone.store');
    Route::middleware('can:vodafone.editar')->put('/vodafone/{vodafone}', [VodafoneController::class, 'update'])->name('vodafone.update');
    Route::middleware('can:vodafone.eliminar')->delete('/vodafone/{vodafone}', [VodafoneController::class, 'destroy'])->name('vodafone.destroy');
});

// ===================
// RUTA BACKUP SI FALLA
// ===================
Route::fallback(function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
});


Route::middleware(['auth', 'verified'])->group(function () {

    Route::prefix('prueba')->group(function () {
        Route::get('/', function () {
            return Inertia::render('Waha');
        })->name('prueba');

        Route::resource('chips', ChipController::class);
        Route::resource('companias', CompaniaController::class);
        Route::resource('estados', EstadoController::class);
        Route::resource('usuarios', UsuarioController::class);
        Route::resource('registros', RegistroController::class);
    });
});



Route::middleware(['auth'])->group(function () {

    /* ============================================================
     * 🔹 VISTAS PRINCIPALES (Inertia)
     * ============================================================ */
    Route::get('/conexion', [WahaConexionController::class, 'index'])
        ->name('conexion.view');

    Route::get('/campania', fn() => inertia('Mensajeria/Campania'))
        ->name('campania.view');

    // 🔸 Vista de reportes (Mensajeria/Reporte.vue)
    Route::get('/reporte', [LogController::class, 'index'])
        ->name('reporte.view');


    /* ============================================================
     * 🔹 WAHA CONEXIONES
     * ============================================================ */
    Route::prefix('conexion')->name('conexion.')->group(function () {
        Route::get('/list', [WahaConexionController::class, 'list'])->name('list');
        Route::post('/', [WahaConexionController::class, 'store'])->name('store');
        Route::put('/{waha}', [WahaConexionController::class, 'update'])->name('update');
        Route::delete('/{waha}', [WahaConexionController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/test', [WahaConexionController::class, 'test'])->name('test');
        Route::get('/{id}/sesiones', [WahaConexionController::class, 'sesionesRealTime'])->name('sesiones');
    });


    /* ============================================================
     * 🔹 MENSAJES DIRECTOS Y SESIONES WAHA
     * ============================================================ */
    Route::prefix('waha')->name('waha.')->group(function () {
        Route::post('/enviar', [WahaConexionController::class, 'enviarMensaje'])->name('enviar');
        Route::get('/sesiones/disponibles', [WahaConexionController::class, 'sesionesDisponibles'])->name('sesiones');
    });


    /* ============================================================
     * 🔹 CAMPAÑAS
     * ============================================================ */
    Route::prefix('campania')->name('campania.')->group(function () {
        Route::get('/list', [CampaniaController::class, 'index'])->name('list');
        Route::post('/', [CampaniaController::class, 'store'])->name('store');
        Route::get('/{campania}', [CampaniaController::class, 'show'])->name('show');
        Route::put('/{campania}', [CampaniaController::class, 'update'])->name('update');
        Route::delete('/{campania}', [CampaniaController::class, 'destroy'])->name('destroy');
        Route::post('/{campania}/iniciar', [CampaniaController::class, 'iniciar'])->name('iniciar');
        Route::post('/{campania}/enviar', [CampaniaController::class, 'enviar'])->name('enviar');
    });


    /* ============================================================
     * 🔹 REPORTES (Logs de Envíos y Respuestas - API)
     * ============================================================ */
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/envios/{campania}', [LogController::class, 'getEnviosByCampania'])->name('envios.campania');
        Route::get('/respuestas/{campania}', [LogController::class, 'getRespuestasByCampania'])->name('respuestas.campania');
    });
});