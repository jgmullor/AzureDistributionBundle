<?php
/**
 * WindowsAzure DistributionBundle
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace WindowsAzure\DistributionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Windows Azure Configuration
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('windows_azure');

        $rootNode
            ->children()
                ->arrayNode('deployment')
                    ->children()
                        ->scalarNode('ignoreVCS')->defaultValue(true)->end()
                        ->variableNode('include')->defaultValue(array())->end()
                        ->variableNode('exclude')->defaultValue(array())->end()
                        ->variableNode('ignorePatterns')->defaultValue(array())->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

