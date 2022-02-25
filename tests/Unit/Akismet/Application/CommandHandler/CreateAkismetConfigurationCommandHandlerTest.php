<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\Command;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Api\AkismetApiInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\CreateAkismetConfigurationCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\CreateAkismetConfigurationCommandHandler;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\DataMapper\AkismetConfigurationDataMapperInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetConfigurationNotFoundException;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetConfigurationRepositoryInterface;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\CreateAkismetConfigurationCommandHandler
 *
 * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\CreateAkismetConfigurationCommand
 */
class CreateAkismetConfigurationCommandHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<AkismetConfigurationRepositoryInterface> */
    private ObjectProphecy $repository;

    /** @var ObjectProphecy<AkismetConfigurationDataMapperInterface> */
    private ObjectProphecy $dataMapper;

    /** @var ObjectProphecy<AkismetApiInterface> */
    private ObjectProphecy $api;

    private CreateAkismetConfigurationCommandHandler $commandHandler;

    protected function setUp(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationRepositoryInterface> */
        $repository = $this->repository = self::prophesize(AkismetConfigurationRepositoryInterface::class);
        /** @var ObjectProphecy<AkismetConfigurationDataMapperInterface> */
        $dataMapper = $this->dataMapper = self::prophesize(AkismetConfigurationDataMapperInterface::class);
        /** @var ObjectProphecy<AkismetApiInterface> */
        $api = $this->api = self::prophesize(AkismetApiInterface::class);

        $this->commandHandler = new CreateAkismetConfigurationCommandHandler(
            $repository->reveal(),
            $dataMapper->reveal(),
            $api->reveal(),
        );
    }

    public function testInvokeHavingExistingAkismetConfiguration(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = self::prophesize(AkismetConfigurationInterface::class);
        $this->repository->getByFormId(1)->willReturn($akismetConfiguration->reveal());

        $this->dataMapper->mapData($akismetConfiguration, ['foo' => 'bar'])->shouldBeCalled();
        $akismetConfiguration->getApiKey()->willReturn('api-key');
        $akismetConfiguration->getSiteUrl()->willReturn('site-url');
        $this->api->verifyKey($akismetConfiguration->reveal())->shouldBeCalled();

        $command = new CreateAkismetConfigurationCommand(1, ['foo' => 'bar']);

        self::assertSame(
            $akismetConfiguration->reveal(),
            $this->commandHandler->__invoke($command)
        );
    }

    /**
     * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetConfigurationNotFoundException
     * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\ModelNotFoundException
     */
    public function testInvokeHavingNewAkismetConfiguration(): void
    {
        $this->repository->getByFormId(1)->willThrow(new AkismetConfigurationNotFoundException(['formId' => 1]));

        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = self::prophesize(AkismetConfigurationInterface::class);
        $this->repository->create(1)->willReturn($akismetConfiguration->reveal());
        $this->repository->add($akismetConfiguration->reveal())->shouldBeCalled();

        $this->dataMapper->mapData($akismetConfiguration, ['foo' => 'bar'])->shouldBeCalled();
        $akismetConfiguration->getApiKey()->willReturn(null);
        $akismetConfiguration->getSiteUrl()->willReturn(null);
        $this->api->verifyKey(Argument::any())->shouldNotBeCalled();

        $command = new CreateAkismetConfigurationCommand(1, ['foo' => 'bar']);

        self::assertSame(
            $akismetConfiguration->reveal(),
            $this->commandHandler->__invoke($command)
        );
    }
}
