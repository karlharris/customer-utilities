<?php

declare(strict_types=1);

namespace KarlHarris\Service;

use DateTime;
use Exception;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\RepositoryIterator;

class CustomerIteratorService
{
    public function __construct(
        private readonly EntityRepository $customerRepo,
    ) {
    }

    public function getAllActiveCustomersWithoutOrders(Context $context, int $limit = 500): RepositoryIterator
    {
        $criteria = $this->getBaseActiveCriteria()
            ->addFilter($this->getWithoutOrdersFilter())
            ->setLimit($limit)
        ;

        return new RepositoryIterator($this->customerRepo, $context, $criteria);
    }

    /** @throws Exception */
    public function getOlderActiveCustomersWithoutOrders(
        Context $context,
        int $days,
        int $limit = 500
    ): RepositoryIterator {

        $criteria = $this->getBaseActiveCriteria()
            ->addFilter($this->getWithoutOrdersFilter())
            ->addFilter($this->getOlderThanFilter($days))
            ->setLimit($limit)
        ;

        return new RepositoryIterator($this->customerRepo, $context, $criteria);
    }

    /** @throws Exception */
    private function getOlderThanFilter(int $days): RangeFilter
    {
        $dateTime = new DateTime('-' . $days . ' days');
        return new RangeFilter('customer.createdAt', [
            RangeFilter::LT => $dateTime->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }

    private function getWithoutOrdersFilter(): RangeFilter
    {
        return new RangeFilter('customer.orderCount', [
            RangeFilter::LTE => 0
        ]);
    }

    private function getBaseActiveCriteria(): Criteria
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
