<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception;

interface FormSubmissionIsSpamExceptionInterface extends \Throwable
{
    public function getSpamStrategy(): string;

    public function getReason(): string;
}
