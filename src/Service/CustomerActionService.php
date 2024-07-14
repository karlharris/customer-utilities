<?php

declare(strict_types=1);

namespace KarlHarris\Service;

use RuntimeException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Throwable;

class CustomerActionService
{
    public function __construct(
        private readonly EntityRepository $customerRepo,
    ) {
    }

    public function deleteCustomer(string $id, Context $context): void
    {
        try {
            $this->customerRepo->delete([['id' => $id]], $context);
        } catch (Throwable $e) {
            throw new RuntimeException(
                sprintf('Could not delete customer (id: %s)', $id),
                0,
                $e
            );
        }
    }
}
