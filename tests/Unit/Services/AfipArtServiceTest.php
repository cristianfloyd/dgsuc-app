<?php

namespace Tests\Unit\Services;

use Mockery;
use Tests\TestCase;
use App\Models\AfipArt;
use App\Services\AfipArtService;
use App\Repositories\AfipArtRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AfipArtServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $afipArtService;
    protected $afipArtRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->afipArtRepository = Mockery::mock(AfipArtRepository::class);
        $this->afipArtService = new AfipArtService($this->afipArtRepository);
    }

    /** @test */
    public function it_can_get_all_afip_art()
    {
        $this->afipArtRepository->shouldReceive('getAll')->once()->andReturn(collect([new AfipArt]));

        $afipArts = $this->afipArtService->getAll();

        $this->assertCount(1, $afipArts);
    }

    /** @test */
    public function it_can_find_afip_art_by_cuil()
    {
        $afipArt = new AfipArt(['cuil_original' => '20123456789']);

        $this->afipArtRepository->shouldReceive('findByCuil')->with('20123456789')->once()->andReturn($afipArt);

        $foundAfipArt = $this->afipArtService->findByCuil('20123456789');

        $this->assertEquals('20123456789', $foundAfipArt->cuil_original);
    }

    /** @test */
    public function it_can_create_afip_art()
    {
        $data = [
            'cuil_formateado' => '20-12345678-9',
            'cuil_original' => '20123456789',
            'apellido_y_nombre' => 'Juan Perez',
            'nacimiento' => '1980-01-01',
            'sueldo' => '50000',
            'sexo' => 'M',
            'nro_legaj' => 1234,
            'establecimiento' => 'Establecimiento 1',
            'tarea' => 'Tarea 1',
            'concepto' => 1,
        ];

        $this->afipArtRepository->shouldReceive('create')->with($data)->once()->andReturn(new AfipArt($data));

        $afipArt = $this->afipArtService->create(new \App\Http\Requests\AfipArtRequest($data));

        $this->assertEquals('20123456789', $afipArt->cuil_original);
    }

    /** @test */
    public function it_can_update_afip_art()
    {
        $data = [
            'apellido_y_nombre' => 'Juan Perez Updated',
        ];

        $this->afipArtRepository->shouldReceive('update')->with('20123456789', $data)->once()->andReturn(true);

        $updated = $this->afipArtService->update('20123456789', new \App\Http\Requests\AfipArtRequest($data));

        $this->assertTrue($updated);
    }

    /** @test */
    public function it_can_delete_afip_art()
    {
        $this->afipArtRepository->shouldReceive('delete')->with('20123456789')->once()->andReturn(true);

        $deleted = $this->afipArtService->delete('20123456789');

        $this->assertTrue($deleted);
    }
}
