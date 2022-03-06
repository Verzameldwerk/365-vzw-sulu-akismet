<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Application\App\AkismetApi;

use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Api\AkismetApiInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;

class TestAkismetApi implements AkismetApiInterface
{
    public function verifyKey(AkismetConfigurationInterface $configuration): void
    {
    }

    public function checkComment(AkismetConfigurationInterface $configuration, array $params): string
    {
        return self::RESULT_HAM;
    }

    public function submitSpam(AkismetConfigurationInterface $configuration, array $params): void
    {
    }

    public function submitHam(AkismetConfigurationInterface $configuration, array $params): void
    {
    }
}
