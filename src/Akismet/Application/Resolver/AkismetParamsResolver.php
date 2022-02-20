<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Resolver;

use Sulu\Bundle\FormBundle\Entity\Dynamic;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class AkismetParamsResolver implements AkismetParamsResolverInterface
{
    private RequestStack $requestStack;
    private ?string $honeypotField;

    public function __construct(RequestStack $requestStack, ?string $honeypotField)
    {
        $this->requestStack = $requestStack;
        $this->honeypotField = $honeypotField;
    }

    public function resolve(FormInterface $form): array
    {
        return array_merge(
            [
                'comment_type' => 'contact-form',
                'comment_date_gmt' => date('c'),
            ],
            $this->resolveRequestParams(),
            $this->resolveFormParams($form)
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveFormParams(FormInterface $form): array
    {
        if (!$form->isSubmitted()) {
            throw new \RuntimeException('The AkismetParamsResolver can only work with submitted forms');
        }

        $data = $form->getData();

        if (!$data instanceof Dynamic) {
            throw new \RuntimeException('The AkismetParamsResolver can only work with sulu forms');
        }

        $params = [
            'comment_author' => implode(' ', array_filter([
                $data->getFirstName(),
                $data->getLastName(),
            ])),
            'comment_author_email' => $data->getEmail(),
            'comment_content' => $data->getTextarea() ?: $data->getText(),
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
