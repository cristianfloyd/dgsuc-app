<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Afip\Resources\AfipRelacionesActivasResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AfipRelacionesActivasResourceTest extends TestCase
{
    // use RefreshDatabase;

    public function test_can_view_index_page(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(AfipRelacionesActivasResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_can_create_record(): void
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
