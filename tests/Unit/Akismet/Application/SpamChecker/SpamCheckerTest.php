<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\SpamChecker;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Sulu\Bundle\FormBundle\Configuration\FormConfigurationInterface;
use Symfony\Component\Form\FormInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\SpamChecker\SpamChecker;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\SpamChecker\SpamCheckerInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Exception\FormSubmissionIsSpamException;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\SpamChecker\SpamChecker
 */
class SpamCheckerTest extends TestCase
{
    use ProphecyTrait;

    private SpamChecker $spamChecker;

    protected function setUp(): void
    {
        $spamChecker1 = $this->prophesize(SpamCheckerInterface::class);
        $spamChecker1->check(Argument::any(), Argument::any())->willThrow(
            new FormSubmissionIsSpamException(SpamCheckerInterface::SPAM_STRATEGY_SPAM, 'spamChecker1')
        );

        $spamChecker2 = $this->prophesize(SpamCheckerInterface::class);
        $spamChecker2->check(Argument::any(), Argument::any())->willThrow(
            new FormSubmissionIsSpamException(SpamCheckerInterface::SPAM_STRATEGY_NO_SAVE, 'spamChecker2')
        );

        $spamChecker3 = $this->prophesize(SpamCheckerInterface::class);
        $spamChecker3->check(Argument::any(), Argument::any())->willThrow(
            new FormSubmissionIsSpamException(SpamCheckerInterface::SPAM_STRATEGY_NO_EMAIL, 'spamChecker3')
        );

        $spamChecker4 = $this->prophesize(SpamCheckerInterface::class);
        $spamChecker4->check(Argument::any(), Argument::any())->willThrow(
            new FormSubmissionIsSpamException(SpamCheckerInterface::SPAM_STRATEGY_NO_SAVE, 'spamChecker4')
        );

        $this->spamChecker = new SpamChecker([
            $spamChecker1->reveal(),
            $spamChecker2->reveal(),
            $spamChecker3->reveal(),
            $spamChecker4->reveal(),
        ]);
    }

    public function testCheck(): void
    {
        $this->expectExceptionObject(
            new FormSubmissionIsSpamException(SpamCheckerInterface::SPAM_STRATEGY_NO_SAVE, 'spamChecker2')
        );

        $form = $this->prophesize(FormInterface::class);
        $formConfiguration = $this->prophesize(FormConfigurationInterface::class);

        $this->spamChecker->check($form->reveal(), $formConfiguration->reveal());
    }
}
