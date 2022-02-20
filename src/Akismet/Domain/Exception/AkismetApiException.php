<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception;

use Sulu\Component\Rest\Exception\TranslationErrorMessageExceptionInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;

final class AkismetApiException extends \Exception implements TranslationErrorMessageExceptionInterface, UnrecoverableExceptionInterface
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    public function getMessageTranslationKey(): string
    {
        return 'verzameldwerk_akismet.akismet_error';
    }

    public function getMessageTranslationParameters(): array
    {
        return [
            '{message}' => $this->getMessage(),
        ];
    }
}
