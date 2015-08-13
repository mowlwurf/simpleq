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
        // parent queue id
        $prototypeParent = $rootNode->children()->arrayNode('queue');
        $prototype = $prototypeParent->prototype('array');
        $prototype->cannotBeEmpty();
        // child type
        $prototype->children()->scalarNode('type');
        // child history
        $prototype->children()->booleanNode('history');
        // child delete_on_failure
        $prototype->children()->booleanNode('delete_on_failure');
        // child worker(s)
        $workerChild = $prototype->children()->arrayNode('worker');
        $workerChildChilds = $workerChild->prototype('array');
        // worker child class
        $workerChildChilds->children()->scalarNode('class');
        // worker child limit
        $workerChildChilds->children()->integerNode('limit');
        // worker child task
        $workerChildChilds->children()->scalarNode('task');
        // worker child retry
        $workerChildChilds->children()->integerNode('retry');
        // worker child max_load
        $workerChildChilds->children()->integerNode('max_load');

        return $treeBuilder;
    }
}
