<?php

declare(strict_types=1);

namespace Catalog\Update\Application;

use Catalog\Search\Application\CatalogItemView;
use Psr\Log\LoggerInterface;
use Throwable;
use function json_encode;

final class UpdateCatalog
{
    public function __construct(
        private CatalogWriter $catalogWriter,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(CatalogItemView $itemView): void
    {
        try {
            $this->catalogWriter->update($itemView);
            $this->logger->info('Catalog item was updated: '. json_encode($itemView->toArray(), JSON_THROW_ON_ERROR));
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
