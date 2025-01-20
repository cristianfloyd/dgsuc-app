<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\AfipArt;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AfipArtTest extends TestCase
{
    // use RefreshDatabase;

    /** @test */
    public function it_can_create_an_afip_art()
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

        $afipArt = AfipArt::create($data);

        $this->assertDatabaseHas('suc.afip_art', $data);
    }
}
