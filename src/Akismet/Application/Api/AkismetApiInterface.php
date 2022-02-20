<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Api;

use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetApiException;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;

interface AkismetApiInterface
{
    public const RESULT_HAM = 'ham';
    public const RESULT_SPAM = 'spam';
    public const RESULT_DISCARD = 'discard';

    /**
     * @throws AkismetApiException
     */
    public function verifyKey(AkismetConfigurationInterface $configuration): void;

    /**
     * @param array<string, mixed> $params
     *
     * @throws AkismetApiException
     */
    public function checkComment(AkismetConfigurationInterface $configuration, array $params): string;

    /**
     * @param array<string, mixed> $params
     *
     * @throws AkismetApiException
     */
    public function submitSpam(AkismetConfigurationInterface $configuration, array $params): void;

    /**
     * @param array<string, mixed> $params
     *
     * @throws AkismetApiException
     */
    public function submitHam(AkismetConfigurationInterface $configuration, array $params): void;
}
