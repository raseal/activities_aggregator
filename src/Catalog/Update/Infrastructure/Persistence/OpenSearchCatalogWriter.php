<?php

declare(strict_types=1);

namespace Catalog\Update\Infrastructure\Persistence;

use Catalog\Search\CatalogItemView;
use Catalog\Update\Application\CatalogWriter;
use OpenSearch\Client;

final class OpenSearchCatalogWriter implements CatalogWriter
{
    private const string INDEX = 'catalog-search';

    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function update(CatalogItemView $catalogItemView): void
    {
        $this->client->update([
            'index' => self::INDEX,
            'id' => $catalogItemView->id,
            'body' => [
                'doc' => $catalogItemView->toArray(),
                'doc_as_upsert' => true,
            ]
        ]);
    }

    public function deleteById(string $id): void
    {
        $this->client->delete([
            'index' => self::INDEX,
            'id' => $id,
        ]);
    }
}
