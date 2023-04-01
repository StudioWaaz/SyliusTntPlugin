<?php

declare(strict_types=1);

namespace Waaz\SyliusTntPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress UnusedVariable
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('waaz_sylius_tnt');
        $rootNode = $treeBuilder->getRootNode();
        $this->addGlobalSection($rootNode);

        return $treeBuilder;
    }

    protected function addGlobalSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->scalarNode('username')
                    ->defaultValue('login')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('password')
                    ->defaultValue('password')
                    ->cannotBeEmpty()
                ->end()
                ->booleanNode('sandbox')
                    ->defaultTrue()
                ->end()
                ->enumNode('weightUnit')
                    ->cannotBeEmpty()
                    ->values(['kg', 'g'])
                    ->defaultValue('g')
                ->end()
            ->end()
        ;
    }
}