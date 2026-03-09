<?php

use App\Http\Controllers\Auth\Office365Controller;
use App\Http\Controllers\Auth\TobaLoginController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\UsersController;
use App\Livewire\AfipImportCrudo;
use App\Livewire\AfipMapucheMiSimplificacion;
use App\Livewire\AfipMapucheMiSimplificacionTable;
use App\Livewire\AfipMiSimplificacion;
use App\Livewire\AfipRelacionesActivas;
use App\Livewire\AsignacionPresupuestaria\AsignacionForm;
use App\Livewire\BuscarColumna;
use App\Livewire\BuscarComentario;
use App\Livewire\Clicker;
use App\Livewire\CompareCuils;
use App\Livewire\ContactUs;
use App\Livewire\ConvertirTabla;
use App\Livewire\Dh21;
use App\Livewire\FileEncoding;
use App\Livewire\MapucheSicossTable;
use App\Livewire\Modal;
use App\Livewire\PanelSelector;
use App\Livewire\ParaMiSimplificacion;
use App\Livewire\RegisterForm;
use App\Livewire\ReporteLiquidacion;
use App\Livewire\Reportes\OrdenPagoReporte;
use App\Livewire\ShowCuilDetails;
use App\Livewire\SicossImporter;
use App\Livewire\TestCuils;
use App\Livewire\TodoList;
use App\Livewire\Uploadtxt;
use App\Livewire\UserList;
use App\Livewire\UsersTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/user/register', [RegisterForm::class, 'create'])->name('registerform.create');
Route::livewire('/user/register', RegisterForm::class)->name('registerform');

// Rutas de autenticación Toba con prefijo (para compatibilidad con código existente)
Route::prefix('toba-legacy')->group(function (): void {
    Route::get('/login', [TobaLoginController::class, 'showLoginForm'])->name('toba.login.form');
    Route::post('/login', [TobaLoginController::class, 'login'])->name('toba.login');
    Route::post('/logout', [TobaLoginController::class, 'logout'])->name('toba.logout');

    // Rutas adicionales Toba
    Route::get('/password/change', [TobaLoginController::class, 'showChangePasswordForm'])->name('toba.password.change');
    Route::get('/two-factor/verify', [TobaLoginController::class, 'showTwoFactorForm'])->name('toba.two-factor.verify');
});

// Panel Toba ahora está disponible en /toba (manejado por FilamentPHP)




Route::middleware(['auth:sanctum', \App\Http\Middleware\DatabaseConnectionMiddleware::class])
    ->group(function (): void {
        // Rutas protegidas que requieren autenticación y gestión de conexión BD
    });


Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', ])->group(function (): void {
    Route::livewire('/selector-panel', PanelSelector::class)->middleware('auth')->name('panel-selector');
    Route::livewire('/clicker', Clicker::class)->name('clicker');
    Route::get('/suc', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::livewire('/todos', TodoList::class)->name('todos');
    Route::post('/todos', [TodoList::class, 'create'])->name('todos.create');
    Route::livewire('/user/list', UserList::class)->name('userlist');
    Route::livewire('/contactus', ContactUs::class)->name('contact-us');
    Route::livewire('/modal', Modal::class)->name('modal');
    Route::livewire('/userstable', UsersTable::class)->name('datatable');
    Route::get('/', function () {
        return view('index');
    })->name('index');
    Route::livewire('/imputacion', AsignacionForm::class)->name('imputacion');
    Route::livewire('/afip', AfipMiSimplificacion::class)->name('MiSimplificacion');  // Raiz para la app de mapuche-afip mi simplificacion
    Route::livewire('/afip/subir-archivo', Uploadtxt::class)->name('importar'); // 1.- paso subir archivos
    Route::livewire('/afip/relaciones-activas', AfipRelacionesActivas::class)->name('afiprelacionesactivas'); // 2.- paso relaciones activas
    Route::match(['get', 'post'], '/afip/sicossimporter', SicossImporter::class)->name('mapuche-sicoss'); // 3.- paso mapuche sicoss
    Route::match(['get', 'post'], '/afip/compare-cuils', CompareCuils::class)->name('compare-cuils'); //  4.- paso comparar cuils

    Route::livewire('/reporte/orden-pago', OrdenPagoReporte::class)->name('reporte-orden-pago');
    Route::get('/reporte/orden-pago-pdf', function () {
        return view('reporte');
    })->name('reporte-orden-pago-pdf');



    Route::livewire('/misimplificaciontable', ParaMiSimplificacion::class)->name('misimplificaciontable');
    Route::livewire('/afip/convertir', ConvertirTabla::class)->name('convertir');
    // Route::livewire('/afip/mapuchemisim', AfipMapucheMiSimplificacion::class)->name('afip.mapuche.misimplificacion');
    Route::livewire('/afip/mapuche-sicoss-table', MapucheSicossTable::class)->name('mapuche-sicoss-table');
    Route::livewire('/afip/altas-mi-simplificacion', ShowCuilDetails::class)->name('altas');
    Route::livewire('/afip/testcuils', TestCuils::class)->name('testcuils');
    Route::livewire('/importar-crudo', AfipImportCrudo::class)->name('upload');
    Route::livewire('buscar-columna', BuscarColumna::class)->name('buscar-columna');
    Route::livewire('buscar-comentario', BuscarComentario::class)->name('buscar-comentario');
    Route::livewire('dh21', Dh21::class)->name('dh21');
    Route::get('/users', [UsersController::class, 'index'])->name('users.index');
    Route::livewire('reporteLiquidacion', ReporteLiquidacion::class)->name('reporteLiquidacion');
    Route::livewire('/encoding', FileEncoding::class)->name('encoding');
    Route::livewire('/prueba', ParaMiSimplificacion::class)->name('prueba');

    Route::livewire('/afip/misim', AfipMapucheMiSimplificacionTable::class)->name('afip.misimplificacion.table');
});

Route::get('documentation/download', [DocumentationController::class, 'download'])
    ->name('documentation.download')
    ->middleware(['auth']);

Route::get('/documentation', [DocumentationController::class, 'index'])
    ->name('documentation.index');

Route::get('/documentation/{slug}', [DocumentationController::class, 'show'])
    ->name('documentation.show');

// Ruta para descargar archivos SICOSS
Route::get('/afip/sicoss/download', function (Request $request) {
    $path = base64_decode($request->path);

    if (!file_exists($path)) {
        abort(404, 'Archivo no encontrado');
    }

    $extension = pathinfo($path, \PATHINFO_EXTENSION);
    $contentType = $extension === 'txt' ? 'text/plain' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    $fileName = basename($path);

    return response()->download($path, $fileName, [
        'Content-Type' => $contentType,
    ])->deleteFileAfterSend();
})->name('afip.sicoss.download')->middleware(['auth']);


Route::get('auth/microsoft', [Office365Controller::class, 'redirectToProvider'])->name('auth.office365');
Route::get('auth/microsoft/callback', [Office365Controller::class, 'handleProviderCallback']);
