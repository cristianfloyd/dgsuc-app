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
use App\Livewire\CompareCuils;
use App\Livewire\FileEncoding;
use App\Livewire\RegisterForm;
use App\Livewire\BuscarColumna;
use App\Livewire\MapucheSicoss;
use App\Livewire\ConvertirTabla;
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
use App\Livewire\AfipMapucheMiSimplificacion;
use App\Livewire\AfipMapucheMiSimplificacionTable;

Route::post('/user/register', [RegisterForm::class, 'create'])->name('registerform.create');
Route::get('/user/register', RegisterForm::class)->name('registerform');
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {
    Route::get('/clicker', Clicker::class)->name('clicker');
    Route::get('/suc', function () { return view('dashboard'); })->name('dashboard');
    Route::get('/todos', TodoList::class )->name('todos');
    Route::post('/todos', [TodoList::class, 'create'])->name('todos.create');
    Route::get('/user/list',UserList::class)->name('userlist');
    Route::get('/contactus', ContactUs::class)->name('contact-us');
    Route::get('/modal', Modal::class)->name('modal');
    Route::get('/userstable', UsersTable::class)->name('datatable');
    Route::get('/', function () { return view('index'); })->name('index');
    Route::get('/afip', AfipMiSimplificacion::class)->name('MiSimplificacion');  // Raiz para la app de mapuche-afip mi simplificacion
    Route::get('/afip/subir-archivo', Uploadtxt::class)->name('importar'); // 1.- paso subir archivos
    Route::get('/afip/relaciones-activas', AfipRelacionesActivas::class)->name('afiprelacionesactivas');
    Route::get('/afip/mapuchesicoss', MapucheSicoss::class)->name('mapuche-sicoss');
    Route::post('/afip/mapuchesicoss', MapucheSicoss::class)->name('mapuche-sicoss');


    Route::get('/test-column-metadata', function () {
        $columnMetadata = app(ColumnMetadata::class);
        dd($columnMetadata->getWidths());
    });


    Route::get('/afip/convertir',ConvertirTabla::class)->name('convertir');
    Route::get('/afip/mapuchemisim', AfipMapucheMiSimplificacion::class)->name('mapuchemisim');
    Route::get('/afip/mapuche-sicoss-table', MapucheSicossTable::class)->name('mapuche-sicoss-table');
    Route::get('/afip/compare-cuils', CompareCuils::class)->name('compare-cuils');
    Route::post('/afip/compare-cuils', CompareCuils::class)->name('compare-cuils');
    Route::get('/afip/altas-mi-simplificacion', ShowCuilDetails::class)->name('altas');
    Route::get('/afip/testcuils', TestCuils::class)->name('testcuils');
    Route::get('/importar-crudo'  , AfipImportCrudo::class )->name('upload');
    Route::get('buscar-columna', BuscarColumna::class)->name('buscar-columna');
    Route::get('buscar-comentario', BuscarComentario::class)->name('buscar-comentario');
    Route::get('dh21', Dh21::class)->name('dh21');
    Route::get('/users', [UsersController::class, 'index'])->name('users.index');
    Route::get('reporteLiquidacion', ReporteLiquidacion::class)->name('reporteLiquidacion');
    Route::get('/encoding', FileEncoding::class)->name('encoding');
    Route::get('/prueba', ParaMiSimplificacion::class)->name('misimplificacion');

    Route::get('/afip/misim', AfipMapucheMiSimplificacionTable::class)->name('misim');
});
