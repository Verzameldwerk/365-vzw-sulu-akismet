<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Resolver;

use Symfony\Component\Form\FormInterface;

interface AkismetParamsResolverInterface
{
    /**
     * @return array<string, mixed>
     */
    public function resolve(FormInterface $form, int $suluFormId): array;
}
