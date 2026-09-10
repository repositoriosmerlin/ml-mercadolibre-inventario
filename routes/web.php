<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MercadoLibre\OAuthController;
use App\Http\Controllers\Usuarios\PermisoController;
use App\Http\Controllers\Usuarios\RolController;
use App\Http\Controllers\Usuarios\UsuarioController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Entrada
|--------------------------------------------------------------------------
| La raíz no muestra pantalla propia: manda al login si no hay sesión,
| o al módulo si ya la hay.
*/
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('usuarios.index')
        : redirect()->route('login');
})->name('inicio');

/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Menú "Usuarios"
|--------------------------------------------------------------------------
| Agrupa las tres secciones del módulo: usuarios, roles y permisos.
| Los verbos de recurso están en español (crear / editar) — ver
| AppServiceProvider::boot().
|
| cambio --- 10/09/2026: cada acción declara su permiso con 'can'. Estas
| declaraciones son además la fuente del catálogo: App\Support\CatalogoDePermisos
| recorre las rutas registradas y siembra la tabla 'permisos' desde aquí. Un
| permiso que no aparezca en alguna ruta no existe para el sistema.
*/
Route::middleware('auth')->group(function () {
    // cambio --- 10/09/2026: las cuentas no se eliminan, se deshabilitan. De
    // ahí que no exista 'destroy' y sí la ruta de estado.
    Route::resource('usuarios', UsuarioController::class)
        ->parameters(['usuarios' => 'usuario'])
        ->except(['destroy'])
        ->middlewareFor(['index', 'show'], 'can:usuarios.ver')
        ->middlewareFor(['create', 'store'], 'can:usuarios.crear')
        ->middlewareFor(['edit', 'update'], 'can:usuarios.editar');

    Route::put('usuarios/{usuario}/estado', [UsuarioController::class, 'cambiarEstado'])
        ->middleware('can:usuarios.deshabilitar')
        ->name('usuarios.estado');

    Route::resource('roles', RolController::class)
        ->parameters(['roles' => 'rol'])
        ->middlewareFor(['index', 'show'], 'can:roles.ver')
        ->middlewareFor(['create', 'store'], 'can:roles.crear')
        ->middlewareFor(['edit', 'update'], 'can:roles.editar')
        ->middlewareFor('destroy', 'can:roles.eliminar');

    // cambio --- 10/09/2026: el catálogo se lista y se le pueden agregar
    // permisos, nada más. Sin editar ni eliminar: cambiar la clave de un
    // permiso ya asignado a un rol lo desconectaría del middleware que lo
    // exige, y el síntoma sería un 403 sin explicación.
    Route::resource('permisos', PermisoController::class)
        ->parameters(['permisos' => 'permiso'])
        ->only(['index', 'create', 'store'])
        ->middlewareFor('index', 'can:permisos.ver')
        ->middlewareFor(['create', 'store'], 'can:permisos.crear');

    // Asignaciones cruzadas del módulo.
    Route::put('usuarios/{usuario}/roles', [UsuarioController::class, 'sincronizarRoles'])
        ->middleware('can:usuarios.asignar')
        ->name('usuarios.roles.sync');

    Route::put('roles/{rol}/permisos', [RolController::class, 'sincronizarPermisos'])
        ->middleware('can:roles.asignar')
        ->name('roles.permisos.sync');
});

/*
|--------------------------------------------------------------------------
| Mercado Libre
|--------------------------------------------------------------------------
| cambio --- 10/09/2026: autorización OAuth de la cuenta de Mercado Libre.
|
| 'callback' queda detrás de 'auth' a propósito. MELI solo devuelve el navegador
| del operador —que ya venía logueado en Merlin—, así que la sesión sigue viva y
| exigirla evita que un tercero canjee un code contra nuestra instalación.
|
| La ruta debe quedar en /meli/callback exactamente: es lo registrado en el
| DevCenter y MELI compara carácter por carácter.
|
| cambio --- 10/09/2026: 'conectar' declara su permiso (resuelve el TODO que
| estaba aquí). 'callback' no lleva 'can': lo invoca el redirect de MELI, y
| exigir un permiso ahí rompería el canje del code a mitad del flujo.
*/
Route::middleware('auth')->prefix('meli')->name('meli.')->group(function () {
    Route::get('conectar', [OAuthController::class, 'conectar'])
        ->middleware('can:meli.conectar')
        ->name('conectar');

    Route::get('callback', [OAuthController::class, 'callback'])->name('callback');
});
