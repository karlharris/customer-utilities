<?php declare(strict_types=1);

namespace KarlHarris\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

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
}
