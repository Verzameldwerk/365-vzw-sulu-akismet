<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Domain\Model;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetRequest;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetRequest
 */
class AkismetRequestTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @doesNotPerformAssertions
     */
    public function testConstruct(): void
    {
        $akismetConfiguration = $this->createAkismetConfiguration();
        $akismetRequest = new AkismetRequest(
            $akismetConfiguration,
            ['foo' => 'bar'],
            true
        );
    }

    public function testGetAkismetConfiguration(): void
    {
        $akismetConfiguration = $this->createAkismetConfiguration();
        $akismetRequest = $this->createAkismetRequest(['akismetConfiguration' => $akismetConfiguration]);

        self::assertSame($akismetConfiguration, $akismetRequest->getAkismetConfiguration());
    }

    public function testGetRequestParams(): void
    {
        $requestParams = ['foo' => 'bar'];
        $akismetRequest = $this->createAkismetRequest(['requestParams' => $requestParams]);

        self::assertSame($requestParams, $akismetRequest->getRequestParams());
    }

    public function testGetSetSpam(): void
    {
        $akismetRequest = $this->createAkismetRequest(['spam' => false]);

        self::assertFalse($akismetRequest->isSpam());
        $akismetRequest->setSpam(true);
        self::assertTrue($akismetRequest->isSpam());
    }

    private function createAkismetConfiguration(): AkismetConfigurationInterface
    {
        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = self::prophesize(AkismetConfigurationInterface::class);

        return $akismetConfiguration->reveal();
    }

    /**
     * @param array{
     *     akismetConfiguration?: AkismetConfigurationInterface,
     *     requestParams?: array<string, mixed>,
     *     spam?: bool,
     * } $data
     */
    private function createAkismetRequest(array $data = []): AkismetRequest
    {
        return new AkismetRequest(
            $data['akismetConfiguration'] ?? $this->createAkismetConfiguration(),
            $data['requestParams'] ?? [],
            $data['spam'] ?? false,
        );
    }
}
