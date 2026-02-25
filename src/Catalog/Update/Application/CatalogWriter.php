<?php

declare(strict_types=1);

namespace Catalog\Update\Application;

use Catalog\Search\CatalogItemView;

interface CatalogWriter
{
    public function update(CatalogItemView $catalogItemView): void;

    public function deleteById(string $id): void;
}
