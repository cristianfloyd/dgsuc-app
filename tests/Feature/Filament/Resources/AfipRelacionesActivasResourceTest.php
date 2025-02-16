<?php

namespace Tests\Feature\Filament\Resources;

use Tests\TestCase;
use App\Models\User;
use App\Models\AfipRelacionesActivas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Filament\Afip\Resources\AfipRelacionesActivasResource;

class AfipRelacionesActivasResourceTest extends TestCase
{
    // use RefreshDatabase;

    public function test_can_view_index_page()
    {
        $this->actingAs(User::factory()->create());

        $this->get(AfipRelacionesActivasResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_can_create_record()
    {
        $this->actingAs(User::factory()->create());

        $this->post(AfipRelacionesActivasResource::getUrl('create'), [
            'periodo_fiscal' => '202401',
            'cuil' => '20123456789',
            'codigo_movimiento' => '00',
            // ... otros campos requeridos
        ])->assertRedirect();

        $this->assertDatabaseHas('afip_relaciones_activas', [
            'periodo_fiscal' => '202401',
            'cuil' => '20123456789',
        ]);
    }
}
