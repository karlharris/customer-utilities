<?php

declare(strict_types=1);

namespace KarlHarris\ScheduledTask;

use KarlHarris\Service\CustomerActionService;
use KarlHarris\Service\CustomerIteratorService;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: CustomerCleanUpTask::class)]
final class CustomerCleanUpHandler extends ScheduledTaskHandler
{
    public function __construct(
        private readonly EntityRepository $scheduledTaskRepo,
        private readonly LoggerInterface $logger,
        private readonly CustomerIteratorService $customerIterator,
        private readonly CustomerActionService $customerAction,
    ) {
        parent::__construct($scheduledTaskRepo, $logger);
    }

    public function run(): void
    {
        $context = Context::createDefaultContext();

        $iterator = $this->customerIterator->getActiveCustomersWithoutOrders($context);

        if (0 >= $iterator->getTotal()) {
            return;
        }

        foreach ($iterator->fetchIds() as $customerId) {
            $this->customerAction->deleteCustomer($customerId, $context);
        }
    }
}
