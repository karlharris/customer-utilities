<?php

declare(strict_types=1);

namespace KarlHarris\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\RepositoryIterator;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

class CustomerIteratorService
{
    public function __construct(
        private readonly EntityRepository $customerRepo,
    ) {
    }

    public function getActiveCustomersWithoutOrders(Context $context, int $limit = 500): RepositoryIterator
    {
        $criteria = $this->getBaseActiveCriteria()
            ->addFilter(new RangeFilter('customer.orderCount', [
                RangeFilter::LTE => 0
            ]))
            ->setLimit($limit)
        ;

        return new RepositoryIterator($this->customerRepo, $context, $criteria);
    }

    public function getBaseActiveCriteria(): Criteria
    {
        return (new Criteria())
            ->addFilter(new EqualsFilter('customer.active', true))
            /**
             * we need the sorting to secure the limit/offset works as intended
             * @see https://developer.shopware.com/docs/guides/plugins/plugins/framework/data-handling/reading-data.html#using-the-repositoryiterator
             */
            ->addSorting(new FieldSorting('customer.id'))
        ;
    }
}
