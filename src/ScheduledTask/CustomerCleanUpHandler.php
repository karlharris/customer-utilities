<?php

declare(strict_types=1);

namespace KarlHarris\ScheduledTask;

use Exception;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use KarlHarris\Service\CustomerActionService;
use KarlHarris\Service\CustomerIteratorService;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

#[AsMessageHandler(handles: CustomerCleanUpTask::class)]
final class CustomerCleanUpHandler extends ScheduledTaskHandler
{
    public function __construct(
        private readonly EntityRepository $scheduledTaskRepo,
        private readonly LoggerInterface $logger,
        private readonly CustomerIteratorService $customerIterator,
        private readonly CustomerActionService $customerAction,
        private readonly SystemConfigService $systemConfig,
    ) {
        parent::__construct($scheduledTaskRepo, $logger);
    }

    /** @throws Exception */
    public function run(): void
    {
        $context = Context::createDefaultContext();
        $olderThan = $this->systemConfig->getInt('CustomerUtilities.config.cuCustomersOlderThanDays');

        $iterator = $this->customerIterator->getOlderActiveCustomersWithoutOrders($context, $olderThan);

        if (0 >= $iterator->getTotal()) {
            return;
        }

        foreach ($iterator->fetchIds() as $customerId) {
            $this->customerAction->deleteCustomer($customerId, $context);
        }
    }
}
