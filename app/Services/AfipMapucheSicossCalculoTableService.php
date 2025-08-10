<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Tables\AfipMapucheSicossCalculoTableDefinition;
use App\Services\Abstract\AbstractTableService;

class AfipMapucheSicossCalculoTableService extends AbstractTableService
{
    public function __construct(
        private readonly AfipMapucheSicossCalculoTableDefinition $definition,
    ) {
    }

    public function getTableDefinition(): array
    {
        return $this->definition->getColumns();
    }

    public function getTableName(): string
    {
        return 'suc.afip_mapuche_sicoss_calculos';
    }

    /**
     * @inheritDoc
     */
    protected function getIndexes(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    protected function getTablePopulationQuery(): string
    {
        return '';
    }
}
