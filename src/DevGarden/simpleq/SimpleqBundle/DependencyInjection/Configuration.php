<?php

namespace DevGarden\simpleq\SimpleqBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('simpleq');
        $firstChild = $rootNode->children()->arrayNode('workers');
        $firstChild->treatNullLike(array());
        $prototype = $firstChild->prototype('array');
        $prototype->cannotBeEmpty();
        $firstChildChild = $prototype->children()->scalarNode('class');
        $secondChildChild = $prototype->children()->integerNode('limit');
        $secondChild = $rootNode->children()->arrayNode('queues');
        $secondChild->treatNullLike(array());
        $secondChild->prototype('scalar');
        /**$rootNode
            ->children()
                ->arrayNode('workers')
                    ->prototype('array')
                    ->children()
                        ->scalarNode('class')->end()
                        ->integerNode('limit')->end()
                    ->end()
                ->end()
                ->arrayNode('queues')
                    ->treatNullLike(array())
                    ->prototype('scalar')
                ->end()
            ->end()
        ;*/

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
