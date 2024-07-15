<?php declare(strict_types=1);

namespace KarlHarris\Command;

use KarlHarris\Service\CustomerActionService;
use KarlHarris\Service\CustomerIteratorService;
use Shopware\Core\Framework\Context;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CustomerCleanUpCommand extends Command
{
    protected static $defaultName = 'kh:customer:clean-up';

    public function __construct(
        private readonly CustomerIteratorService $customerIterator,
        private readonly CustomerActionService $customerAction,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Clean up registered customers without orders.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Customer clean up');
        $context = Context::createDefaultContext();

        $iterator = $this->customerIterator->getAllActiveCustomersWithoutOrders($context);

        if (0 >= $iterator->getTotal()) {
            $io->info('Nothing to do!');
            return Command::SUCCESS;
        }

        $io->info('Clean up starts!');
        $io->progressStart($iterator->getTotal());

        foreach ($iterator->fetchIds() as $customerId) {
            $this->customerAction->deleteCustomer($customerId, $context);
            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->newLine();
        $io->success('Customers cleaned up!');

        return Command::SUCCESS;
    }
}
