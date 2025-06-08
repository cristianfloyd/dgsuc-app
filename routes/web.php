<?php

use App\Livewire\Dh21;
use App\Livewire\Modal;
use App\Livewire\Clicker;
use App\Livewire\TodoList;
use App\Livewire\UserList;
use App\Livewire\ContactUs;
use App\Livewire\TestCuils;
use App\Livewire\Uploadtxt;
use App\Livewire\UsersTable;
use Illuminate\Http\Request;
use App\Livewire\CompareCuils;
use App\Livewire\FileEncoding;
use App\Livewire\RegisterForm;
use App\Livewire\BuscarColumna;
use App\Livewire\PanelSelector;
use App\Livewire\ConvertirTabla;
use App\Livewire\SicossImporter;
use App\services\ColumnMetadata;
use App\Livewire\AfipImportCrudo;
use App\Livewire\ShowCuilDetails;
use App\Livewire\BuscarComentario;
use App\Livewire\MapucheSicossTable;
use App\Livewire\ReporteLiquidacion;
use Illuminate\Support\Facades\Route;
use App\Livewire\AfipMiSimplificacion;
use App\Livewire\ParaMiSimplificacion;
use App\Livewire\AfipRelacionesActivas;
use App\Http\Controllers\UsersController;
use App\Livewire\Reportes\OrdenPagoReporte;
use App\Livewire\AfipMapucheMiSimplificacion;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\Auth\Office365Controller;
use App\Livewire\AfipMapucheMiSimplificacionTable;
use App\Livewire\AsignacionPresupuestaria\AsignacionForm;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

Route::post('/user/register', [RegisterForm::class, 'create'])->name('registerform.create');
Route::get('/user/register', RegisterForm::class)->name('registerform');






Route::middleware(['auth:sanctum', \App\Http\Middleware\DatabaseConnectionMiddleware::class])
    ->group(function () {
        // Rutas protegidas que requieren autenticación y gestión de conexión BD
    });


Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {
    Route::get('/selector-panel', PanelSelector::class)->middleware('auth')->name('panel-selector');
    Route::get('/clicker', Clicker::class)->name('clicker');
    Route::get('/suc', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::get('/todos', TodoList::class)->name('todos');
    Route::post('/todos', [TodoList::class, 'create'])->name('todos.create');
    Route::get('/user/list', UserList::class)->name('userlist');
    Route::get('/contactus', ContactUs::class)->name('contact-us');
    Route::get('/modal', Modal::class)->name('modal');
    Route::get('/userstable', UsersTable::class)->name('datatable');
    Route::get('/', function () {
        return view('index');
    })->name('index');
    Route::get('/imputacion', AsignacionForm::class)->name('imputacion');
    Route::get('/afip', AfipMiSimplificacion::class)->name('MiSimplificacion');  // Raiz para la app de mapuche-afip mi simplificacion
    Route::get('/afip/subir-archivo', Uploadtxt::class)->name('importar'); // 1.- paso subir archivos
    Route::get('/afip/relaciones-activas', AfipRelacionesActivas::class)->name('afiprelacionesactivas'); // 2.- paso relaciones activas
    Route::get('/afip/sicossimporter', SicossImporter::class)->name('mapuche-sicoss'); // 3.- paso mapuche sicoss
    Route::post('/afip/sicossimporter', SicossImporter::class)->name('mapuche-sicoss');
    Route::get('/afip/compare-cuils', CompareCuils::class)->name('compare-cuils'); //  4.- paso comparar cuils
    Route::post('/afip/compare-cuils', CompareCuils::class)->name('compare-cuils');

    Route::get('/reporte/orden-pago', OrdenPagoReporte::class)->name('reporte-orden-pago');
    Route::get('/reporte/orden-pago-pdf', function () {
        return view('reporte');
    })->name('reporte-orden-pago-pdf');
    Route::get('/test-column-metadata', function () {
        $columnMetadata = app(ColumnMetadata::class);
    });


    Route::get('/misimplificaciontable', ParaMiSimplificacion::class)->name('misimplificaciontable');
    Route::get('/afip/convertir', ConvertirTabla::class)->name('convertir');
    Route::get('/afip/mapuchemisim', AfipMapucheMiSimplificacion::class)->name('mapuchemisim');
    Route::get('/afip/mapuche-sicoss-table', MapucheSicossTable::class)->name('mapuche-sicoss-table');
    Route::get('/afip/altas-mi-simplificacion', ShowCuilDetails::class)->name('altas');
    Route::get('/afip/testcuils', TestCuils::class)->name('testcuils');
    Route::get('/importar-crudo', AfipImportCrudo::class)->name('upload');
    Route::get('buscar-columna', BuscarColumna::class)->name('buscar-columna');
    Route::get('buscar-comentario', BuscarComentario::class)->name('buscar-comentario');
    Route::get('dh21', Dh21::class)->name('dh21');
    Route::get('/users', [UsersController::class, 'index'])->name('users.index');
    Route::get('reporteLiquidacion', ReporteLiquidacion::class)->name('reporteLiquidacion');
    Route::get('/encoding', FileEncoding::class)->name('encoding');
    Route::get('/prueba', ParaMiSimplificacion::class)->name('prueba');

    Route::get('/afip/misim', AfipMapucheMiSimplificacionTable::class)->name('misim');
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

    $extension = pathinfo($path, PATHINFO_EXTENSION);
    $contentType = $extension === 'txt' ? 'text/plain' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    $fileName = basename($path);

    return response()->download($path, $fileName, [
        'Content-Type' => $contentType,
    ])->deleteFileAfterSend();
})->name('afip.sicoss.download')->middleware(['auth']);


Route::get('auth/microsoft', [Office365Controller::class, 'redirectToProvider'])->name('auth.office365');
Route::get('auth/microsoft/callback', [Office365Controller::class, 'handleProviderCallback']);