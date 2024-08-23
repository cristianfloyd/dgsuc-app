<?php

namespace Database\Seeders;

use App\Models\OrigenesModel;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OrigenesModelSeeder extends Seeder
{
    public function run(): void
    {
        $origenes = [
            ['name' => 'afip'],
            ['name' => 'mapuche'],
        ];

        foreach ($origenes as $origen) {
            OrigenesModel::firstOrCreate($origen);
        }
    }
}
