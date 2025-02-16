<?php

namespace Tests\Unit\Repositories;

use App\Repositories\AfipArtRepository;
use App\Models\AfipMapucheArt as AfipArt;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AfipArtRepositoryTest extends TestCase
{
    // use RefreshDatabase;

    protected $afipArtRepository;

    /** @test */
    public function it_can_get_all_afip_art()
    {
        AfipArt::factory()->count(3)->create();

        $afipArts = $this->afipArtRepository->getAll();

        $this->assertCount(3, $afipArts);
    }

    /** @test */
    public function it_can_find_afip_art_by_cuil()
    {
        $afipArt = AfipArt::factory()->create();

        $foundAfipArt = $this->afipArtRepository->findByCuil($afipArt->cuil_original);

        $this->assertEquals($afipArt->cuil_original, $foundAfipArt->cuil_original);
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

        $afipArt = $this->afipArtRepository->create($data);

        $this->assertDatabaseHas('suc.afip_art', $data);
    }

    /** @test */
    public function it_can_update_afip_art()
    {
        $afipArt = AfipArt::factory()->create();

        $data = [
            'apellido_y_nombre' => 'Juan Perez Updated',
        ];

        $updated = $this->afipArtRepository->update($afipArt->cuil_original, $data);

        $this->assertTrue($updated);
        $this->assertDatabaseHas('suc.afip_art', $data);
    }

    /** @test */
    public function it_can_delete_afip_art()
    {
        $afipArt = AfipArt::factory()->create();

        $deleted = $this->afipArtRepository->delete($afipArt->cuil_original);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('suc.afip_art', ['cuil_original' => $afipArt->cuil_original]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->afipArtRepository = new AfipArtRepository();
    }
}
