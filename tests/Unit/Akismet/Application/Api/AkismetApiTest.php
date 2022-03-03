<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\Api;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\FormBundle\Entity\Form;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Api\AkismetApi;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Api\AkismetApiInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetApiException;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfiguration;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Api\AkismetApi
 *
 * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfiguration
 * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetApiException
 */
class AkismetApiTest extends TestCase
{
    private MockHttpClient $httpClient;
    private AkismetApiInterface $api;

    protected function setUp(): void
    {
        $this->httpClient = new MockHttpClient();
        $this->api = new AkismetApi($this->httpClient);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testVerifyKey(): void
    {
        $this->httpClient->setResponseFactory(
            new MockResponse('valid')
        );

        $akismetConfiguration = $this->createAkismetConfiguration();
        $akismetConfiguration->setApiKey('valid-api-key');
        $akismetConfiguration->setSiteUrl('valid-site-url');

        $this->api->verifyKey($akismetConfiguration);
    }

    public function testVerifyKeyInvalid(): void
    {
        $this->httpClient->setResponseFactory(
            new MockResponse('invalid', ['response_headers' => ['x-akismet-debug-help' => 'Some details about the error']])
        );

        $akismetConfiguration = $this->createAkismetConfiguration();
        $akismetConfiguration->setApiKey('invalid-api-key');
        $akismetConfiguration->setSiteUrl('invalid-site-url');

        static::expectException(AkismetApiException::class);
        static::expectExceptionMessage('Some details about the error');

        $this->api->verifyKey($akismetConfiguration);
    }

    public function testVerifyKeyMissingApiKey(): void
    {
        $akismetConfiguration = $this->createAkismetConfiguration();
        $akismetConfiguration->setApiKey(null);
        $akismetConfiguration->setSiteUrl('valid-site-url');

        static::expectException(AkismetApiException::class);
        static::expectExceptionMessage('Cannot call akismet api, because "apiKey" is missing');

        $this->api->verifyKey($akismetConfiguration);
    }

    public function testVerifyKeyMissingSiteUrl(): void
    {
        $akismetConfiguration = $this->createAkismetConfiguration();
        $akismetConfiguration->setApiKey('valid-api-key');
        $akismetConfiguration->setSiteUrl(null);

        static::expectException(AkismetApiException::class);
        static::expectExceptionMessage('Cannot call akismet api, because "siteUrl" is missing');

        $this->api->verifyKey($akismetConfiguration);
    }

    public function testCheckCommentHam(): void
    {
        $this->httpClient->setResponseFactory(
            new MockResponse('false')
        );

        $akismetConfiguration = $this->createAkismetConfiguration();
        $akismetConfiguration->setActive(true);
        $akismetConfiguration->setApiKey('valid-api-key');
        $akismetConfiguration->setSiteUrl('valid-site-url');

        $this->assertSame(
            'ham',
            $this->api->checkComment($akismetConfiguration, [])
        );
    }

    public function testCheckCommentSpam(): void
    {
        $this->httpClient->setResponseFactory(
            new MockResponse('true')
        );

        $akismetConfiguration = $this->createAkismetConfiguration();
        $akismetConfiguration->setActive(true);
        $akismetConfiguration->setApiKey('valid-api-key');
        $akismetConfiguration->setSiteUrl('valid-site-url');

        $this->assertSame(
            'spam',
            $this->api->checkComment($akismetConfiguration, [])
        );
    }

    public function testCheckCommentDiscard(): void
    {
        $this->httpClient->setResponseFactory(
            new MockResponse('true', ['response_headers' => ['x-akismet-pro-tip' => 'discard']])
        );

        $akismetConfiguration = $this->createAkismetConfiguration();
        $akismetConfiguration->setActive(true);
        $akismetConfiguration->setApiKey('valid-api-key');
        $akismetConfiguration->setSiteUrl('valid-site-url');

        $this->assertSame(
            'discard',
            $this->api->checkComment($akismetConfiguration, [])
        );
    }

    public function testCheckCommentInactiveConfiguration(): void
    {
        $akismetConfiguration = $this->createAkismetConfiguration();
        $akismetConfiguration->setActive(false);
        $akismetConfiguration->setApiKey('valid-api-key');
        $akismetConfiguration->setSiteUrl('valid-site-url');

        static::expectException(AkismetApiException::class);
        static::expectExceptionMessage('Cannot call akismet api, because Akismet configuration is not active');

        $this->api->checkComment($akismetConfiguration, []);
    }

    public function testCheckCommentMissingApiKey(): void
    {
        $akismetConfiguration = $this->createAkismetConfiguration();
        $akismetConfiguration->setActive(true);
        $akismetConfiguration->setApiKey(null);
        $akismetConfiguration->setSiteUrl('valid-site-url');

        static::expectException(AkismetApiException::class);
        static::expectExceptionMessage('Cannot call akismet api, because "apiKey" is missing');

        $this->api->checkComment($akismetConfiguration, []);
    }

    public function testCheckCommentMissingSiteUrl(): void
    {
        $akismetConfiguration = $this->createAkismetConfiguration();
        $akismetConfiguration->setActive(true);
        $akismetConfiguration->setApiKey('valid-api-key');
        $akismetConfiguration->setSiteUrl(null);

        static::expectException(AkismetApiException::class);
        static::expectExceptionMessage('Cannot call akismet api, because "siteUrl" is missing');

        $this->api->checkComment($akismetConfiguration, []);
    }

    public function testCheckCommentOtherError(): void
    {
        $this->httpClient->setResponseFactory(
            new MockResponse('', ['response_headers' => ['x-akismet-debug-help' => 'Some details about the error']])
        );

        $akismetConfiguration = $this->createAkismetConfiguration();
        $akismetConfiguration->setActive(true);
        $akismetConfiguration->setApiKey('valid-api-key');
        $akismetConfiguration->setSiteUrl('valid-site-url');

        static::expectException(AkismetApiException::class);
        static::expectExceptionMessage('Some details about the error');

        $this->api->checkComment($akismetConfiguration, []);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testSubmitHam(): void
    {
        $this->httpClient->setResponseFactory(
            new MockResponse('Thanks for making the web a better place.')
        );

        $akismetConfiguration = $this->createAkismetConfiguration();
        $akismetConfiguration->setActive(true);
        $akismetConfiguration->setApiKey('valid-api-key');
        $akismetConfiguration->setSiteUrl('valid-site-url');

        $this->api->submitHam($akismetConfiguration, []);
    }

    public function testSubmitHamInactiveConfiguration(): void
    {
        $akismetConfiguration = $this->createAkismetConfiguration();
        $akismetConfiguration->setActive(false);
        $akismetConfiguration->setApiKey('valid-api-key');
        $akismetConfiguration->setSiteUrl('valid-site-url');

        static::expectException(AkismetApiException::class);
        static::expectExceptionMessage('Cannot call akismet api, because Akismet configuration is not active');

        $this->api->submitHam($akismetConfiguration, []);
    }

    public function testSubmitHamMissingApiKey(): void
    {
        $akismetConfiguration = $this->createAkismetConfiguration();
        $akismetConfiguration->setActive(true);
        $akismetConfiguration->setApiKey(null);
        $akismetConfiguration->setSiteUrl('valid-site-url');

        static::expectException(AkismetApiException::class);
        static::expectExceptionMessage('Cannot call akismet api, because "apiKey" is missing');

        $this->api->submitHam($akismetConfiguration, []);
    }

    public function testSubmitHamMissingSiteUrl(): void
    {
        $akismetConfiguration = $this->createAkismetConfiguration();
        $akismetConfiguration->setActive(true);
        $akismetConfiguration->setApiKey('valid-api-key');
        $akismetConfiguration->setSiteUrl(null);

        static::expectException(AkismetApiException::class);
        static::expectExceptionMessage('Cannot call akismet api, because "siteUrl" is missing');

        $this->api->submitHam($akismetConfiguration, []);
    }

    public function testSubmitHamOtherError(): void
    {
        $this->httpClient->setResponseFactory(
            new MockResponse('', ['response_headers' => ['x-akismet-debug-help' => 'Some details about the error']])
        );

        $akismetConfiguration = $this->createAkismetConfiguration();
        $akismetConfiguration->setActive(true);
        $akismetConfiguration->setApiKey('valid-api-key');
        $akismetConfiguration->setSiteUrl('valid-site-url');

        static::expectException(AkismetApiException::class);
        static::expectExceptionMessage('Some details about the error');

        $this->api->submitHam($akismetConfiguration, []);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testSubmitSpam(): void
    {
        $this->httpClient->setResponseFactory(
            new MockResponse('Thanks for making the web a better place.')
        );

        $akismetConfiguration = $this->createAkismetConfiguration();
        $akismetConfiguration->setActive(true);
        $akismetConfiguration->setApiKey('valid-api-key');
        $akismetConfiguration->setSiteUrl('valid-site-url');

        $this->api->submitSpam($akismetConfiguration, []);
    }

    public function testSubmitSpamInactiveConfiguration(): void
    {
        $akismetConfiguration = $this->createAkismetConfiguration();
        $akismetConfiguration->setActive(false);
        $akismetConfiguration->setApiKey('valid-api-key');
        $akismetConfiguration->setSiteUrl('valid-site-url');

        static::expectException(AkismetApiException::class);
        static::expectExceptionMessage('Cannot call akismet api, because Akismet configuration is not active');

        $this->api->submitSpam($akismetConfiguration, []);
    }

    public function testSubmitSpamMissingApiKey(): void
    {
        $akismetConfiguration = $this->createAkismetConfiguration();
        $akismetConfiguration->setActive(true);
        $akismetConfiguration->setApiKey(null);
        $akismetConfiguration->setSiteUrl('valid-site-url');

        static::expectException(AkismetApiException::class);
        static::expectExceptionMessage('Cannot call akismet api, because "apiKey" is missing');

        $this->api->submitSpam($akismetConfiguration, []);
    }

    public function testSubmitSpamMissingSiteUrl(): void
    {
        $akismetConfiguration = $this->createAkismetConfiguration();
        $akismetConfiguration->setActive(true);
        $akismetConfiguration->setApiKey('valid-api-key');
        $akismetConfiguration->setSiteUrl(null);

        static::expectException(AkismetApiException::class);
        static::expectExceptionMessage('Cannot call akismet api, because "siteUrl" is missing');

        $this->api->submitSpam($akismetConfiguration, []);
    }

    public function testSubmitSpamOtherError(): void
    {
        $this->httpClient->setResponseFactory(
            new MockResponse('', ['response_headers' => ['x-akismet-debug-help' => 'Some details about the error']])
        );

        $akismetConfiguration = $this->createAkismetConfiguration();
        $akismetConfiguration->setActive(true);
        $akismetConfiguration->setApiKey('valid-api-key');
        $akismetConfiguration->setSiteUrl('valid-site-url');

        static::expectException(AkismetApiException::class);
        static::expectExceptionMessage('Some details about the error');

        $this->api->submitSpam($akismetConfiguration, []);
    }

    /**
     * @param array{
     *     form?: Form,
     * } $data
     */
    private function createAkismetConfiguration(array $data = []): AkismetConfigurationInterface
    {
        return new AkismetConfiguration($data['form'] ?? new Form());
    }
}
