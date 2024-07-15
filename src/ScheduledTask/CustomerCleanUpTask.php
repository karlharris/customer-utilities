<?php declare(strict_types=1);

namespace KarlHarris\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CustomerCleanUpTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'kh.customer.clean_up';
    }

    public static function getDefaultInterval(): int
    {
        return 60;
    }

    public static function shouldRun(ParameterBagInterface $bag): bool
    {
        /** TODO: Test! */
        return (bool) $bag->get('CustomerUtilities.config.cuShouldRun');
    }
}
