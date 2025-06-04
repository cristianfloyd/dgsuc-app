<?php

namespace App\Providers;


use App\Repositories\Dh01Repository;
use App\Repositories\Dh11Repository;
use App\Repositories\Dh19Repository;
use App\Repositories\Dh21Repository;
use App\Repositories\Dh61Repository;
use App\Repositories\Dh90Repository;
use App\Repositories\EmbargoRepository;
use App\Services\CategoryUpdateService;
use Illuminate\Support\ServiceProvider;
use App\Repositories\FallecidoRepository;
use App\Contracts\Dh01RepositoryInterface;
use App\Contracts\Dh19RepositoryInterface;
use App\Contracts\Dh21RepositoryInterface;
use App\Repositories\Sicoss\Dh03Repository;
use App\Repositories\RepOrdenPagoRepository;
use App\Repositories\Dh11RepositoryInterface;
use App\Repositories\Dh61RepositoryInterface;
use App\Repositories\Mapuche\Dh21hRepository;
use App\Repositories\SicossReporteRepository;
use App\Repositories\Sicoss\LicenciaRepository;
use App\Contracts\CategoryUpdateServiceInterface;
use App\Contracts\RepOrdenPagoRepositoryInterface;
use App\Contracts\Mapuche\Dh21hRepositoryInterface;
use App\Repositories\Sicoss\SicossEstadoRepository;
use App\Repositories\Sicoss\SicossCalculoRepository;
use App\Repositories\Interfaces\Dh90RepositoryInterface;
use App\Contracts\Repositories\EmbargoRepositoryInterface;
use App\Repositories\Interfaces\FallecidoRepositoryInterface;
use App\Repositories\Sicoss\Contracts\Dh03RepositoryInterface;
use App\Repositories\Decorators\CachingSicossReporteRepository;
use App\Repositories\EloquentAfipMapucheSicossCalculoRepository;
use App\Repositories\Interfaces\SicossReporteRepositoryInterface;
use App\Repositories\Contracts\AfipMapucheSicossCalculoRepository;
use App\Repositories\Sicoss\Contracts\LicenciaRepositoryInterface;
use App\Repositories\Sicoss\Contracts\SicossEstadoRepositoryInterface;
use App\Repositories\Sicoss\Contracts\SicossCalculoRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(Dh01RepositoryInterface::class, Dh01Repository::class);
        $this->app->bind(Dh11RepositoryInterface::class, Dh11Repository::class);
        $this->app->bind(Dh21RepositoryInterface::class, Dh21Repository::class);
        $this->app->bind(Dh61RepositoryInterface::class, Dh61Repository::class);
        $this->app->bind(CategoryUpdateServiceInterface::class, CategoryUpdateService::class);
        $this->app->bind(RepOrdenPagoRepositoryInterface::class, RepOrdenPagoRepository::class);
        $this->app->bind(Dh19RepositoryInterface::class, Dh19Repository::class);
        $this->app->bind(Dh21hRepositoryInterface::class, Dh21hRepository::class);
        $this->app->bind(EmbargoRepositoryInterface::class, EmbargoRepository::class);
        $this->app->bind(
            FallecidoRepositoryInterface::class,
            FallecidoRepository::class
        );
        $this->app->bind(
            abstract: AfipMapucheSicossCalculoRepository::class,
            concrete: EloquentAfipMapucheSicossCalculoRepository::class
        );
        $this->app->bind(
            Dh90RepositoryInterface::class,
            Dh90Repository::class
        );
        $this->app->bind(
            SicossReporteRepositoryInterface::class,
            SicossReporteRepository::class
        );
        $this->app->bind(LicenciaRepositoryInterface::class, LicenciaRepository::class);
        $this->app->bind(Dh03RepositoryInterface::class, Dh03Repository::class);
        $this->app->bind(SicossCalculoRepositoryInterface::class, SicossCalculoRepository::class);
        $this->app->bind(SicossEstadoRepositoryInterface::class, SicossEstadoRepository::class);

        // Registrar la implementación concreta primero
        $this->app->bind(
            SicossReporteRepository::class,
            function ($app) {
            // Aquí puedes inyectar dependencias específicas de SicossReporteRepository si las tuviera
            // Por ejemplo, si necesitara PeriodoFiscalService directamente:
            // return new SicossReporteRepository($app->make(PeriodoFiscalService::class));
            return new SicossReporteRepository(
                $app->make(\App\Services\Mapuche\PeriodoFiscalService::class) // Asumiendo que SicossReporteRepository lo necesita
            );
        });

        // Registrar el decorador para la interfaz
        $this->app->bind(SicossReporteRepositoryInterface::class, function ($app) {
            // Crear la instancia real del repositorio
            $actualRepository = $app->make(SicossReporteRepository::class);

            // Envolverla con el decorador de caché
            return new CachingSicossReporteRepository($actualRepository);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
