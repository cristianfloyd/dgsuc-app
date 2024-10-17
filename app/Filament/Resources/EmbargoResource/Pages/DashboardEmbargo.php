<?php

namespace App\Filament\Resources\EmbargoResource\Pages;

use Filament\Resources\Pages\Page;
use App\Filament\Resources\EmbargoResource;

class DashboardEmbargo extends Page
{
    protected static string $resource = EmbargoResource::class;

    protected static string $view = 'filament.resources.embargo-resource.pages.dashboard-embargo';

}
