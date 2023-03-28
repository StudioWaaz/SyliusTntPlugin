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
                ->scalarNode('username')->isRequired()->end()
                ->scalarNode('password')->isRequired()->end()
                ->booleanNode('sandbox')->isRequired()->end()
            ->end()
        ;
    }
}