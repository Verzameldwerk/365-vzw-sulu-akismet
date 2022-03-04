<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Api;

use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetApiException;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Webmozart\Assert\Assert;

final class AkismetApi implements AkismetApiInterface
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws AkismetApiException
     * @throws ServerExceptionInterface
     */
    public function verifyKey(AkismetConfigurationInterface $configuration): void
    {
        $this->assertValidConfiguration($configuration, false);
        $siteUrl = $configuration->getSiteUrl();
        $apiKey = $configuration->getApiKey();

        $response = $this->request('https://rest.akismet.com/1.1/verify-key', [
            'key' => $apiKey,
            'blog' => $siteUrl,
        ]);

        $content = $response->getContent();

        if ('valid' === $content) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        throw new AkismetApiException($this->getHeaderValue($response->getHeaders(), 'x-akismet-debug-help'));
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws AkismetApiException
     * @throws ServerExceptionInterface
     */
    public function checkComment(AkismetConfigurationInterface $configuration, array $params): string
    {
        $this->assertValidConfiguration($configuration);
        $apiKey = $configuration->getApiKey();

        $response = $this->request(
            sprintf('https://%s.rest.akismet.com/1.1/comment-check', $apiKey),
            $params
        );

        $content = $response->getContent();

        if ('true' === $content) {
            if ('discard' === $this->getHeaderValue($response->getHeaders(), 'x-akismet-pro-tip')) {
                return self::RESULT_DISCARD;
            }

            return self::RESULT_SPAM;
        }

        if ('false' === $content) {
            return self::RESULT_HAM;
        }

        throw new AkismetApiException($this->getHeaderValue($response->getHeaders(), 'x-akismet-debug-help'));
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws AkismetApiException
     * @throws ServerExceptionInterface
     */
    public function submitSpam(AkismetConfigurationInterface $configuration, array $params): void
    {
        $this->submitAs('spam', $configuration, $params);
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws AkismetApiException
     * @throws ServerExceptionInterface
     */
    public function submitHam(AkismetConfigurationInterface $configuration, array $params): void
    {
        $this->submitAs('ham', $configuration, $params);
    }

    /**
     * @param array<string, mixed> $params
     *
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws AkismetApiException
     * @throws ServerExceptionInterface
     */
    private function submitAs(string $submitAs, AkismetConfigurationInterface $configuration, array $params): void
    {
        Assert::oneOf($submitAs, ['spam', 'ham']);

        $this->assertValidConfiguration($configuration);
        $apiKey = $configuration->getApiKey();

        $response = $this->request(
            sprintf('https://%s.rest.akismet.com/1.1/submit-%s', $apiKey, $submitAs),
            $params
        );

        $content = $response->getContent();

        if ('Thanks for making the web a better place.' === $content) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        throw new AkismetApiException($this->getHeaderValue($response->getHeaders(), 'x-akismet-debug-help'));
    }

    /**
     * @param array<string, mixed> $params
     *
     * @throws TransportExceptionInterface
     */
    private function request(string $url, array $params): ResponseInterface
    {
        $params = array_filter(
            array_map(
                function ($value): ?string {
                    return is_scalar($value) ? (string) $value : null;
                },
                $params
            ),
            function ($value) {
                return null !== $value;
            }
        );

        $formData = new FormDataPart($params);

        return $this->client->request('POST', $url, [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToString(),
        ]);
    }

    /**
     * @throws AkismetApiException
     */
    private function assertValidConfiguration(AkismetConfigurationInterface $configuration, bool $requiresActive = true): void
    {
        if ($requiresActive && false === $configuration->isActive()) {
            throw new AkismetApiException('Cannot call akismet api, because Akismet configuration is not active');
        }

        if (!$configuration->getApiKey()) {
            throw new AkismetApiException('Cannot call akismet api, because "apiKey" is missing');
        }

        if (!$configuration->getSiteUrl()) {
            throw new AkismetApiException('Cannot call akismet api, because "siteUrl" is missing');
        }
    }

    /**
     * @param array<string, mixed> $headers
     */
    private function getHeaderValue(array $headers, string $key): string
    {
        $value = $headers[strtolower($key)] ?? null;

        if (\is_array($value)) {
            $value = $value[0] ?? null;
        }

        return (string) $value;
    }
}
