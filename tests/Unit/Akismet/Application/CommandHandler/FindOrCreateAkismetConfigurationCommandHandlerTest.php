<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\CommandHandler;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\FindOrCreateAkismetConfigurationCommand;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\FindOrCreateAkismetConfigurationCommandHandler;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\AkismetConfigurationNotFoundException;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetConfigurationRepositoryInterface;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\CommandHandler\FindOrCreateAkismetConfigurationCommandHandler
 *
 * @uses \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Command\FindOrCreateAkismetConfigurationCommand
 */
class FindOrCreateAkismetConfigurationCommandHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<AkismetConfigurationRepositoryInterface> */
    private ObjectProphecy $repository;

    private FindOrCreateAkismetConfigurationCommandHandler $commandHandler;

    protected function setUp(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationRepositoryInterface> */
        $repository = $this->repository = $this->prophesize(AkismetConfigurationRepositoryInterface::class);

        $this->commandHandler = new FindOrCreateAkismetConfigurationCommandHandler(
            $repository->reveal()
        );
    }

    public function testInvokeHavingExistingAkismetConfiguration(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationInterface> $akismetConfiguration */
        $akismetConfiguration = $this->prophesize(AkismetConfigurationInterface::class);
        $this->repository->getByFormId(1)->willReturn($akismetConfiguration->reveal());

        $command = new FindOrCreateAkismetConfigurationCommand(1);

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
        $akismetConfiguration = $this->prophesize(AkismetConfigurationInterface::class);
        $this->repository->create(1)->willReturn($akismetConfiguration->reveal());
        $this->repository->add($akismetConfiguration->reveal())->shouldBeCalled();

        $command = new FindOrCreateAkismetConfigurationCommand(1);

        self::assertSame(
            $akismetConfiguration->reveal(),
            $this->commandHandler->__invoke($command)
        );
    }
}
