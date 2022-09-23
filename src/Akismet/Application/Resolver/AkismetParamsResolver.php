<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Resolver;

use Sulu\Bundle\FormBundle\Entity\Dynamic;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetConfigurationRepositoryInterface;

final class AkismetParamsResolver implements AkismetParamsResolverInterface
{
    private AkismetConfigurationRepositoryInterface $akismetConfigurationRepository;
    private RequestStack $requestStack;
    private ?string $honeypotField;

    public function __construct(
        AkismetConfigurationRepositoryInterface $akismetConfigurationRepository,
        RequestStack $requestStack,
        ?string $honeypotField
    ) {
        $this->akismetConfigurationRepository = $akismetConfigurationRepository;
        $this->requestStack = $requestStack;
        $this->honeypotField = str_replace(' ', '_', strtolower($honeypotField ?? ''));
    }

    public function resolve(FormInterface $form, int $suluFormId): array
    {
        return array_merge(
            [
                'comment_type' => 'contact-form',
                'comment_date_gmt' => date('c'),
            ],
            $this->resolveRequestParams(),
            $this->resolveFormParams($form, $suluFormId)
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveFormParams(FormInterface $form, int $suluFormId): array
    {
        if (!$form->isSubmitted()) {
            throw new \RuntimeException('The AkismetParamsResolver only works with submitted forms');
        }

        $data = $form->getData();

        if (!$data instanceof Dynamic) {
            throw new \RuntimeException('The AkismetParamsResolver only works with sulu forms');
        }

        $akismetConfiguration = $this->akismetConfigurationRepository->getByFormId($suluFormId);

        $authorNameField = $akismetConfiguration->getAuthorNameField() ?: '';
        if ($authorNameField) {
            $authorNameField = $authorNameField->getKey();
        }

        $authorEmailField = $akismetConfiguration->getAuthorEmailField() ?: '';
        if ($authorEmailField) {
            $authorEmailField = $authorEmailField->getKey();
        }

        $contentField = $akismetConfiguration->getContentField() ?: '';
        if ($contentField) {
            $contentField = $contentField->getKey();
        }

        $params = [
            'comment_author' => $data->getField($authorNameField) ?: implode(' ', array_filter([
                $data->getFirstName(),
                $data->getLastName(),
            ])),
            'comment_author_email' => $data->getField($authorEmailField) ?: $data->getEmail(),
            'comment_content' => $data->getField($contentField) ?: $data->getTextarea() ?: $data->getText(),
            'blog_lang' => $data->getLocale(),
        ];

        if ($this->honeypotField) {
            $params['honeypot_field_name'] = $this->honeypotField;
            $params[$this->honeypotField] = $form->get($this->honeypotField)->getData();
        }

        return $params;
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveRequestParams(): array
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            throw new \RuntimeException('The AkismetParamsResolver requires a request object');
        }

        return [
            'user_ip' => $request->getClientIp(),
            'user_agent' => $request->headers->get('user-agent'),
            'referrer' => $request->headers->get('referer'),
            'permalink' => $request->getUri(),
        ];
    }
}
