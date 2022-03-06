<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\Tests\Unit\Akismet\Application\Resolver;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\FormBundle\Entity\Dynamic;
use Sulu\Bundle\FormBundle\Entity\Form;
use Sulu\Bundle\FormBundle\Entity\FormField;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Resolver\AkismetParamsResolver;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Resolver\AkismetParamsResolverInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Repository\AkismetConfigurationRepositoryInterface;

/**
 * @covers \Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\Resolver\AkismetParamsResolver
 */
class AkismetParamsResolverTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<AkismetConfigurationRepositoryInterface>
     */
    private ObjectProphecy $akismetConfigurationRepository;

    /**
     * @var ObjectProphecy<RequestStack>
     */
    private ObjectProphecy $requestStack;

    private AkismetParamsResolverInterface $paramsResolver;

    protected function setUp(): void
    {
        /** @var ObjectProphecy<AkismetConfigurationRepositoryInterface> $akismetConfigurationRepository */
        $akismetConfigurationRepository = $this->akismetConfigurationRepository = $this->prophesize(AkismetConfigurationRepositoryInterface::class);

        /** @var ObjectProphecy<RequestStack> $requestStack */
        $requestStack = $this->requestStack = $this->prophesize(RequestStack::class);

        $this->paramsResolver = new AkismetParamsResolver(
            $akismetConfigurationRepository->reveal(),
            $requestStack->reveal(),
            'honeypotField'
        );
    }

    public function testResolve(): void
    {
        $request = $this->createRequest();
        $this->requestStack->getCurrentRequest()->willReturn($request);

        $akismetConfiguration = $this->prophesize(AkismetConfigurationInterface::class);
        $akismetConfiguration->getAuthorNameField()->willReturn((new FormField())->setKey('authorName'));
        $akismetConfiguration->getAuthorEmailField()->willReturn((new FormField())->setKey('authorEmail'));
        $akismetConfiguration->getContentField()->willReturn((new FormField())->setKey('content'));
        $this->akismetConfigurationRepository->getByFormId(1)->willReturn($akismetConfiguration->reveal());

        $form = $this->createForm([
            'data' => new Dynamic('type', 'typeId', 'en', new Form(), [
                'authorName' => 'Adam Ministrator',
                'authorEmail' => 'admin@example.com',
                'content' => 'Lorem ipsum dolor sit amet',
            ]),
            'children' => [
                'honeypotField' => $this->createForm(['data' => 'HONEYPOT']),
            ],
        ]);

        $this->assertSame(
            [
                'comment_type' => 'contact-form',
                'comment_date_gmt' => date('c'),
                'user_ip' => '1.2.3.4',
                'user_agent' => 'UserAgent',
                'referrer' => 'Referer',
                'permalink' => 'https://example.com/',
                'comment_author' => 'Adam Ministrator',
                'comment_author_email' => 'admin@example.com',
                'comment_content' => 'Lorem ipsum dolor sit amet',
                'blog_lang' => 'en',
                'honeypot_field_name' => 'honeypotField',
                'honeypotField' => 'HONEYPOT',
            ],
            $this->paramsResolver->resolve($form, 1)
        );
    }

    public function testResolveWithoutFieldMapping(): void
    {
        $request = $this->createRequest();
        $this->requestStack->getCurrentRequest()->willReturn($request);

        $akismetConfiguration = $this->prophesize(AkismetConfigurationInterface::class);
        $akismetConfiguration->getAuthorNameField()->willReturn(null);
        $akismetConfiguration->getAuthorEmailField()->willReturn(null);
        $akismetConfiguration->getContentField()->willReturn(null);
        $this->akismetConfigurationRepository->getByFormId(1)->willReturn($akismetConfiguration->reveal());

        $form = $this->createForm([
            'data' => new Dynamic('type', 'typeId', 'en', new Form(), [
                'firstName' => 'Adam',
                'lastName' => 'Ministrator',
                'email' => 'admin@example.com',
                'textarea' => 'Lorem ipsum dolor sit amet',
            ]),
            'children' => [
                'honeypotField' => $this->createForm(['data' => 'HONEYPOT']),
            ],
        ]);

        $this->assertSame(
            [
                'comment_type' => 'contact-form',
                'comment_date_gmt' => date('c'),
                'user_ip' => '1.2.3.4',
                'user_agent' => 'UserAgent',
                'referrer' => 'Referer',
                'permalink' => 'https://example.com/',
                'comment_author' => 'Adam Ministrator',
                'comment_author_email' => 'admin@example.com',
                'comment_content' => 'Lorem ipsum dolor sit amet',
                'blog_lang' => 'en',
                'honeypot_field_name' => 'honeypotField',
                'honeypotField' => 'HONEYPOT',
            ],
            $this->paramsResolver->resolve($form, 1)
        );
    }

    public function testResolveWithoutRequest(): void
    {
        $this->requestStack->getCurrentRequest()->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The AkismetParamsResolver requires a request object');

        $this->paramsResolver->resolve($this->createForm(), 1);
    }

    public function testResolveWithUnsubmittedForm(): void
    {
        $request = $this->createRequest();
        $this->requestStack->getCurrentRequest()->willReturn($request);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The AkismetParamsResolver only works with submitted forms');

        $this->paramsResolver->resolve($this->createForm(['submitted' => false]), 1);
    }

    public function testResolveWithInvalidData(): void
    {
        $request = $this->createRequest();
        $this->requestStack->getCurrentRequest()->willReturn($request);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The AkismetParamsResolver only works with sulu forms');

        $this->paramsResolver->resolve($this->createForm(['data' => 'other']), 1);
    }

    private function createRequest(
        string $uri = 'https://example.com/',
        string $method = 'POST',
        string $clientIp = '1.2.3.4',
        string $userAgent = 'UserAgent',
        string $referer = 'Referer'
    ): Request {
        return Request::create($uri, $method, [], [], [], [
            'REMOTE_ADDR' => $clientIp,
            'HTTP_USER-AGENT' => $userAgent,
            'HTTP_REFERER' => $referer,
        ]);
    }

    /**
     * @param array{
     *     submitted?: bool,
     *     data?: mixed,
     *     children?: FormInterface[],
     * } $data
     */
    private function createForm(array $data = []): FormInterface
    {
        $form = $this->prophesize(FormInterface::class);
        $form->isSubmitted()->willReturn($data['submitted'] ?? true);

        if ($formData = $data['data'] ?? null) {
            $form->getData()->willReturn($formData);
        }

        foreach ($data['children'] ?? [] as $key => $child) {
            $form->get($key)->willReturn($child);
        }

        return $form->reveal();
    }
}
