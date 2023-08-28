<?php

declare(strict_types=1);

namespace Verzameldwerk\Bundle\AkismetBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Application\SpamChecker\SpamCheckerInterface;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetConfiguration;
use Verzameldwerk\Bundle\AkismetBundle\Akismet\Domain\Model\AkismetRequest;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('verzameldwerk_akismet');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->enumNode('akismet_spam_strategy')
                    ->values(SpamCheckerInterface::SPAM_STRATEGIES)
                    ->defaultValue(SpamCheckerInterface::SPAM_STRATEGIES[2])
                ->end()
            ->end();

        $this->addObjectsSection($rootNode);

        return $treeBuilder;
    }

    private function addObjectsSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('objects')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('akismet_configuration')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('model')->defaultValue(AkismetConfiguration::class)->end()
                            ->end()
                        ->end()
                        ->arrayNode('akismet_request')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('model')->defaultValue(AkismetRequest::class)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
